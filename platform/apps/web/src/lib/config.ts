/**
 * Web application configuration.
 *
 * Validates environment variables available to the browser at build time.
 * Uses Zod for type safety — throws at startup if any required variable is missing.
 *
 * IMPORTANT: Only NEXT_PUBLIC_* variables are exposed to the browser.
 * Never put secrets in NEXT_PUBLIC_* variables.
 */
import { z } from 'zod';

const configSchema = z.object({
  apiUrl: z.string().url({
    message: 'NEXT_PUBLIC_API_URL must be a valid URL (e.g. http://localhost:4000)',
  }),
  environment: z.enum(['development', 'test', 'production']).default('development'),
});

function loadConfig() {
  const result = configSchema.safeParse({
    apiUrl: process.env['NEXT_PUBLIC_API_URL'] ?? 'http://localhost:4000',
    environment: process.env['NODE_ENV'] ?? 'development',
  });

  if (!result.success) {
    const errors = result.error.errors.map((e) => `  ${e.path.join('.')}: ${e.message}`).join('\n');
    throw new Error(`Web configuration error:\n${errors}`);
  }

  return result.data;
}

export const config = loadConfig();
export type Config = typeof config;

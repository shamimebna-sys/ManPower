import { z } from 'zod';

/**
 * Environment configuration schema.
 * Validated once at startup — the app will not start if any required
 * variable is missing or has an invalid value.
 *
 * Never add raw `process.env` access outside this module.
 */
const envSchema = z.object({
  // Runtime environment
  NODE_ENV: z.enum(['development', 'test', 'production']).default('development'),

  // Server
  PORT: z.coerce.number().int().positive().max(65535).default(4000),

  // Database (PostgreSQL — new target)
  DATABASE_URL: z
    .string()
    .url()
    .startsWith('postgresql://', {
      message: 'DATABASE_URL must be a valid PostgreSQL connection string (postgresql://...)',
    }),

  // API
  API_VERSION: z.string().default('v1'),

  // CORS — comma-separated list of allowed origins
  CORS_ORIGINS: z
    .string()
    .default('http://localhost:3000')
    .transform((s) => s.split(',').map((o) => o.trim())),

  // Authentication
  SESSION_TTL_HOURS: z.coerce.number().int().min(1).max(720).default(24),
  BCRYPT_ROUNDS: z.coerce.number().int().min(10).max(15).default(12),

  // Initial administrator bootstrap. All three are optional at runtime;
  // the bootstrap command validates them as a complete set.
  BOOTSTRAP_ADMIN_EMAIL: z.string().email().optional(),
  BOOTSTRAP_ADMIN_USERNAME: z.string().min(3).max(100).optional(),
  BOOTSTRAP_ADMIN_PASSWORD: z.string().min(12).max(128).optional(),

  // Logging
  LOG_LEVEL: z
    .enum(['trace', 'debug', 'info', 'warn', 'error', 'fatal'])
    .default('info'),
});

export type Env = z.infer<typeof envSchema>;

/**
 * Validated environment.
 * Import this — do not import process.env directly.
 */
export function validateEnv(raw: Record<string, string | undefined> = process.env): Env {
  const result = envSchema.safeParse(raw);

  if (!result.success) {
    const formatted = result.error.errors
      .map((e) => `  ${e.path.join('.')}: ${e.message}`)
      .join('\n');

    throw new Error(
      `Environment validation failed. Fix the following variables:\n${formatted}\n` +
        `Copy .env.example to .env and fill in the required values.`
    );
  }

  return result.data;
}

// Singleton: validated once at module load in production
let _env: Env | undefined;

export function getEnv(): Env {
  if (!_env) {
    _env = validateEnv();
  }
  return _env;
}

/** Reset singleton (for testing only) */
export function _resetEnv(): void {
  _env = undefined;
}

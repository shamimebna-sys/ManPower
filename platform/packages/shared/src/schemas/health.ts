import { z } from 'zod';

export const HealthResponseSchema = z.object({
  status: z.enum(['ok', 'degraded', 'error']),
  version: z.string(),
  environment: z.string(),
  timestamp: z.string().datetime(),
  uptime: z.number().nonnegative(),
});

export const DbHealthResponseSchema = z.object({
  status: z.enum(['ok', 'error']),
  database: z.object({
    connected: z.boolean(),
    latencyMs: z.number().nonnegative().optional(),
    message: z.string().optional(),
  }),
  timestamp: z.string().datetime(),
});

export type HealthResponseSchema = z.infer<typeof HealthResponseSchema>;
export type DbHealthResponseSchema = z.infer<typeof DbHealthResponseSchema>;

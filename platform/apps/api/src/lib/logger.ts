import pino from 'pino';
import { getEnv } from '../config/env.js';

/**
 * Structured logger using pino.
 *
 * Development: pretty-printed output (uses pino-pretty transport).
 * Production:  JSON newline-delimited logs (ship to log aggregator).
 *
 * SECURITY: Never log secrets, passwords, tokens, or full request bodies.
 * The requestLogger middleware automatically redacts Authorization headers.
 */
export function createLogger() {
  const env = getEnv();
  const isDev = env.NODE_ENV === 'development';

  return pino({
    level: env.LOG_LEVEL,
    // Redact sensitive fields from all log records
    redact: {
      paths: [
        'req.headers.authorization',
        'req.headers.cookie',
        'req.headers["x-api-key"]',
        'body.password',
        'body.token',
        'body.secret',
        '*.password',
        '*.token',
        '*.secret',
      ],
      censor: '[REDACTED]',
    },
    // In development, use pino-pretty for human-readable output
    ...(isDev && {
      transport: {
        target: 'pino-pretty',
        options: {
          colorize: true,
          translateTime: 'HH:MM:ss.l',
          ignore: 'pid,hostname',
        },
      },
    }),
  });
}

// Application-wide logger singleton
export const logger = createLogger();

export type Logger = typeof logger;

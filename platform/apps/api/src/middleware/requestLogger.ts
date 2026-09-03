import type { Request, Response, NextFunction } from 'express';
import { randomUUID } from 'crypto';
import { logger } from '../lib/logger.js';

/**
 * Request logging middleware.
 *
 * - Assigns a unique request ID to every request.
 * - Logs request start and completion with method, path, status, duration.
 * - Attaches requestId to the response header for client-side tracing.
 *
 * SECURITY: Authorization and Cookie headers are redacted by the logger
 * (configured in lib/logger.ts redact rules).
 */
export function requestLogger(req: Request, res: Response, next: NextFunction): void {
  const requestId = (req.headers['x-request-id'] as string | undefined) ?? randomUUID();
  const startTime = Date.now();

  // Attach request ID — available to downstream handlers and error handler
  req.headers['x-request-id'] = requestId;
  res.setHeader('X-Request-Id', requestId);

  logger.info(
    {
      requestId,
      method: req.method,
      url: req.url,
      userAgent: req.headers['user-agent'],
      ip: req.ip,
    },
    'Incoming request'
  );

  res.on('finish', () => {
    const durationMs = Date.now() - startTime;
    const level = res.statusCode >= 500 ? 'error' : res.statusCode >= 400 ? 'warn' : 'info';

    logger[level](
      {
        requestId,
        method: req.method,
        url: req.url,
        statusCode: res.statusCode,
        durationMs,
      },
      'Request completed'
    );
  });

  next();
}

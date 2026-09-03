import type { Request, Response, NextFunction, ErrorRequestHandler } from 'express';
import { ZodError } from 'zod';
import type { ApiErrorResponse } from '@manpower/shared';
import { ERROR_CODES, HTTP_STATUS } from '@manpower/shared';
import { logger } from '../lib/logger.js';

/**
 * Application-level error class.
 * Throw this to return a structured error response.
 */
export class AppError extends Error {
  constructor(
    public readonly statusCode: number,
    public readonly code: string,
    message: string,
    public readonly details?: unknown
  ) {
    super(message);
    this.name = 'AppError';
    Object.setPrototypeOf(this, AppError.prototype);
  }

  static notFound(resource: string): AppError {
    return new AppError(HTTP_STATUS.NOT_FOUND, ERROR_CODES.NOT_FOUND, `${resource} not found`);
  }

  static badRequest(message: string, details?: unknown): AppError {
    return new AppError(HTTP_STATUS.BAD_REQUEST, ERROR_CODES.VALIDATION_ERROR, message, details);
  }

  static forbidden(message = 'Access denied'): AppError {
    return new AppError(HTTP_STATUS.FORBIDDEN, ERROR_CODES.FORBIDDEN, message);
  }

  static unauthorized(message = 'Authentication required'): AppError {
    return new AppError(HTTP_STATUS.UNAUTHORIZED, ERROR_CODES.UNAUTHORIZED, message);
  }

  static conflict(message: string): AppError {
    return new AppError(HTTP_STATUS.CONFLICT, ERROR_CODES.CONFLICT, message);
  }

  static internal(message = 'An unexpected error occurred'): AppError {
    return new AppError(HTTP_STATUS.INTERNAL_SERVER_ERROR, ERROR_CODES.INTERNAL_ERROR, message);
  }
}

/** Build the base error object, only including requestId when it's defined. */
function baseError(
  code: string,
  message: string,
  requestId: string | undefined
): ApiErrorResponse['error'] {
  return requestId !== undefined
    ? { code, message, requestId }
    : { code, message };
}

/**
 * Central error handling middleware.
 * Must be the LAST middleware registered in the Express app.
 *
 * Handles:
 *  - AppError — structured app errors
 *  - ZodError — request validation failures
 *  - Error    — unexpected errors (logged; generic response in production)
 *
 * SECURITY: Stack traces and internal details are NEVER sent in production responses.
 */
export const errorHandler: ErrorRequestHandler = (
  err: unknown,
  req: Request,
  res: Response,
  _next: NextFunction
): void => {
  const isProd = process.env['NODE_ENV'] === 'production';
  const requestId = req.headers['x-request-id'] as string | undefined;

  // ── Zod validation errors ─────────────────────────────────────────────
  if (err instanceof ZodError) {
    const details = err.errors.map((e) => ({
      field: e.path.join('.'),
      message: e.message,
      code: e.code,
    }));

    const body: ApiErrorResponse = {
      success: false,
      error: {
        ...baseError(ERROR_CODES.VALIDATION_ERROR, 'Request validation failed', requestId),
        details,
      },
    };

    res.status(HTTP_STATUS.UNPROCESSABLE_ENTITY).json(body);
    return;
  }

  // ── Application errors ────────────────────────────────────────────────
  if (err instanceof AppError) {
    if (err.statusCode >= 500) {
      logger.error({ err, requestId, url: req.url, method: req.method }, err.message);
    }

    const body: ApiErrorResponse = {
      success: false,
      error: baseError(err.code, err.message, requestId),
    };

    res.status(err.statusCode).json(body);
    return;
  }

  // ── Unexpected errors ─────────────────────────────────────────────────
  const error = err instanceof Error ? err : new Error(String(err));
  logger.error({ err: error, requestId, url: req.url, method: req.method }, 'Unhandled error');

  const message = isProd ? 'An unexpected error occurred' : error.message;
  const body: ApiErrorResponse = {
    success: false,
    error: baseError(ERROR_CODES.INTERNAL_ERROR, message, requestId),
  };

  res.status(HTTP_STATUS.INTERNAL_SERVER_ERROR).json(body);
};

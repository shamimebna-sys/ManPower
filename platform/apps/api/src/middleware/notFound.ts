import type { Request, Response, NextFunction } from 'express';
import type { ApiErrorResponse } from '@manpower/shared';
import { ERROR_CODES, HTTP_STATUS } from '@manpower/shared';

/**
 * 404 catch-all middleware.
 * Must be registered after all route handlers.
 */
export function notFoundHandler(req: Request, res: Response, _next: NextFunction): void {
  const requestId = req.headers['x-request-id'] as string | undefined;

  const error: ApiErrorResponse['error'] =
    requestId !== undefined
      ? { code: ERROR_CODES.NOT_FOUND, message: `Route ${req.method} ${req.path} not found`, requestId }
      : { code: ERROR_CODES.NOT_FOUND, message: `Route ${req.method} ${req.path} not found` };

  const body: ApiErrorResponse = { success: false, error };

  res.status(HTTP_STATUS.NOT_FOUND).json(body);
}

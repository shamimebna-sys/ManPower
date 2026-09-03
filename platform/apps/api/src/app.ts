import express from 'express';
import cookieParser from 'cookie-parser';
import type { Application } from 'express';
import { applySecurityMiddleware } from './middleware/security.js';
import { requestLogger } from './middleware/requestLogger.js';
import { notFoundHandler } from './middleware/notFound.js';
import { errorHandler } from './middleware/errorHandler.js';
import { createRouter } from './routes/index.js';

/**
 * Creates and configures the Express application.
 *
 * Separated from the server bootstrap (index.ts) so that the app can be
 * imported cleanly in tests without starting a real HTTP server.
 *
 * Middleware order (important — do not reorder):
 *  1. Security headers, CORS, body parsing  (applySecurityMiddleware)
 *  2. Request logging + request ID          (requestLogger)
 *  3. Route handlers                        (createRouter)
 *  4. 404 handler                           (notFoundHandler)
 *  5. Error handler                         (errorHandler — must be last)
 */
export function createApp(): Application {
  const app = express();

  // ── 1. Security ───────────────────────────────────────────────────────
  applySecurityMiddleware(app);
  app.use(cookieParser());

  // ── 2. Request logging ────────────────────────────────────────────────
  app.use(requestLogger);

  // ── 3. Routes ─────────────────────────────────────────────────────────
  app.use('/api', createRouter());

  // ── 4. 404 catch-all ──────────────────────────────────────────────────
  app.use(notFoundHandler);

  // ── 5. Centralized error handler (must be last) ───────────────────────
  app.use(errorHandler);

  return app;
}

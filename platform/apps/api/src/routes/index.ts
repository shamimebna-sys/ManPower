import { Router } from 'express';
import { healthRouter } from './health.js';
import { authRouter } from './auth.js';
import { iamRouter } from './iam.js';
import { candidatesRouter } from './candidates.js';

/**
 * Root API router.
 *
 * All routes are mounted here and exported to app.ts.
 * M1 includes only the health routes.
 * Business module routes (M2+) will be added here per milestone approval.
 *
 * Structure:
 *   /api/health      → liveness probe
 *   /api/health/db   → database readiness probe
 *
 *   Future (M2+):
 *   /api/v1/auth     → authentication & session
 *   /api/v1/candidates → candidate module
 *   /api/v1/...      → other business modules
 */
export function createRouter(): Router {
  const router = Router();

  // Health endpoints (no version prefix — consumed by infra tooling)
  router.use('/health', healthRouter);
  router.use('/v1/auth', authRouter);
  router.use('/v1/iam', iamRouter);
  router.use('/v1/candidates', candidatesRouter);

  return router;
}

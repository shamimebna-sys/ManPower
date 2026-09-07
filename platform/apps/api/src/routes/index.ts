import { Router } from 'express';
import { healthRouter } from './health.js';
import { authRouter } from './auth.js';
import { iamRouter } from './iam.js';
import { candidatesRouter } from './candidates.js';
import { partnersRouter } from './partners.js';
import { employerCandidatesRouter } from './employer-candidates.js';
import { teachersRouter } from './teachers.js';
import { classGroupsRouter } from './class-groups.js';
import { classSchedulesRouter } from './class-schedules.js';
import { examsRouter } from './exams.js';
import { examResultsRouter } from './exam-results.js';
import { manpowerTrainingsRouter } from './manpower-trainings.js';
import { createOverseasDocumentsRouter } from './overseas-documents.js';
import { licensesRouter } from './licenses.js';
import { liveStatusRouter } from './live-status.js';

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
  router.use('/v1/partners', partnersRouter);
  router.use('/v1/employer-candidates', employerCandidatesRouter);
  router.use('/v1/teachers', teachersRouter);
  router.use('/v1/class-groups', classGroupsRouter);
  router.use('/v1/class-schedules', classSchedulesRouter);
  router.use('/v1/exams', examsRouter);
  router.use('/v1/exam-results', examResultsRouter);
  router.use('/v1/manpower-trainings', manpowerTrainingsRouter);
  router.use('/v1/overseas', createOverseasDocumentsRouter());
  router.use('/v1/licenses', licensesRouter);
  router.use('/v1/live-status', liveStatusRouter);

  return router;
}

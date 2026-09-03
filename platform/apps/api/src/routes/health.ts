import { Router } from 'express';
import type { Router as ExpressRouter } from 'express';
import type { HealthResponse, DbHealthResponse } from '@manpower/shared';
import { prisma } from '../lib/prisma.js';
import { logger } from '../lib/logger.js';
import { getEnv } from '../config/env.js';

const VERSION = process.env['npm_package_version'] ?? '0.1.0';

export const healthRouter: ExpressRouter = Router();

/**
 * GET /api/health
 *
 * Basic liveness probe — always returns 200 when the API process is running.
 * Used by load balancers and monitoring tools.
 */
healthRouter.get('/', (_req, res) => {
  const env = getEnv();

  const body: HealthResponse = {
    status: 'ok',
    version: VERSION,
    environment: env.NODE_ENV,
    timestamp: new Date().toISOString(),
    uptime: process.uptime(),
  };

  res.status(200).json(body);
});

/**
 * GET /api/health/db
 *
 * Database readiness probe.
 * Returns 200 with connected:true when PostgreSQL is reachable.
 * Returns 503 with connected:false when the database is unavailable.
 * Never throws — always returns a valid JSON response.
 */
healthRouter.get('/db', async (_req, res) => {
  const start = Date.now();

  try {
    await prisma.$queryRaw`SELECT 1`;
    const latencyMs = Date.now() - start;

    const body: DbHealthResponse = {
      status: 'ok',
      database: {
        connected: true,
        latencyMs,
      },
      timestamp: new Date().toISOString(),
    };

    res.status(200).json(body);
  } catch (err) {
    const latencyMs = Date.now() - start;
    const message = err instanceof Error ? err.message : 'Unknown database error';

    logger.warn({ err, latencyMs }, 'Database health check failed');

    const body: DbHealthResponse = {
      status: 'error',
      database: {
        connected: false,
        latencyMs,
        message: process.env['NODE_ENV'] === 'production' ? 'Database unavailable' : message,
      },
      timestamp: new Date().toISOString(),
    };

    // 503 so load balancers can route away from unhealthy instances
    res.status(503).json(body);
  }
});

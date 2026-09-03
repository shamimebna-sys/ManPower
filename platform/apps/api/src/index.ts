/**
 * ManPower Platform — API Entry Point
 *
 * Starts the Express server and manages graceful shutdown.
 * Environment is validated at startup — the process exits on misconfiguration.
 */

import { validateEnv } from './config/env.js';
import { createApp } from './app.js';
import { prisma } from './lib/prisma.js';
import { logger } from './lib/logger.js';

// ── Validate environment before doing anything else ───────────────────────
let env: ReturnType<typeof validateEnv>;
try {
  env = validateEnv();
} catch (err) {
  // Use console here — logger depends on env, which just failed
  console.error('FATAL: Environment validation failed.\n', err instanceof Error ? err.message : err);
  process.exit(1);
}

const app = createApp();

// ── Start HTTP server ─────────────────────────────────────────────────────
const server = app.listen(env.PORT, () => {
  logger.info(
    {
      port: env.PORT,
      environment: env.NODE_ENV,
      version: process.env['npm_package_version'] ?? '0.1.0',
    },
    `ManPower API started on port ${env.PORT}`
  );
});

// ── Graceful shutdown ─────────────────────────────────────────────────────
const SHUTDOWN_TIMEOUT_MS = 10_000;

async function gracefulShutdown(signal: string): Promise<void> {
  logger.info({ signal }, 'Shutdown signal received — starting graceful shutdown');

  // Stop accepting new connections
  server.close(async (err) => {
    if (err) {
      logger.error({ err }, 'Error closing HTTP server');
      process.exit(1);
    }

    try {
      // Disconnect Prisma
      await prisma.$disconnect();
      logger.info('Prisma disconnected');
      logger.info('Graceful shutdown complete');
      process.exit(0);
    } catch (disconnectErr) {
      logger.error({ err: disconnectErr }, 'Error disconnecting Prisma');
      process.exit(1);
    }
  });

  // Force exit if shutdown takes too long
  setTimeout(() => {
    logger.error(`Forced shutdown after ${SHUTDOWN_TIMEOUT_MS}ms timeout`);
    process.exit(1);
  }, SHUTDOWN_TIMEOUT_MS).unref();
}

process.on('SIGTERM', () => void gracefulShutdown('SIGTERM'));
process.on('SIGINT', () => void gracefulShutdown('SIGINT'));

// Handle unhandled rejections — log and exit (let PM2/Docker restart)
process.on('unhandledRejection', (reason) => {
  logger.fatal({ reason }, 'Unhandled promise rejection — shutting down');
  process.exit(1);
});

process.on('uncaughtException', (err) => {
  logger.fatal({ err }, 'Uncaught exception — shutting down');
  process.exit(1);
});

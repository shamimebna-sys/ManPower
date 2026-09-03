import { PrismaClient } from '@prisma/client';
import { getEnv } from '../config/env.js';

/**
 * Prisma client singleton.
 *
 * Uses the global object to prevent multiple client instances in
 * hot-reload (tsx watch) development mode.
 *
 * M1 SCOPE: The schema has no business models yet.
 * The client exposes $queryRaw / $executeRaw for raw SQL (used by the
 * database health check) and $connect / $disconnect for lifecycle management.
 *
 * Business-model methods will be added in M2+ per the approved design.
 */

const globalForPrisma = globalThis as unknown as {
  prisma: PrismaClient | undefined;
};

function createPrismaClient(): PrismaClient {
  const env = getEnv();

  return new PrismaClient({
    log:
      env.NODE_ENV === 'development'
        ? [
            { level: 'query', emit: 'event' },
            { level: 'error', emit: 'stdout' },
            { level: 'warn', emit: 'stdout' },
          ]
        : [{ level: 'error', emit: 'stdout' }],
  });
}

export const prisma: PrismaClient =
  globalForPrisma.prisma ?? createPrismaClient();

if (process.env['NODE_ENV'] !== 'production') {
  globalForPrisma.prisma = prisma;
}

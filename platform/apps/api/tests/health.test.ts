/**
 * Health endpoint tests
 *
 * Tests GET /api/health and GET /api/health/db using supertest.
 * The Prisma client is mocked so no real PostgreSQL is needed.
 */
import { describe, it, expect, vi, beforeEach } from 'vitest';
import request from 'supertest';

// ── Mock environment before app loads ─────────────────────────────────────
vi.mock('../src/config/env', () => ({
  getEnv: () => ({
    NODE_ENV: 'test',
    PORT: 4001,
    DATABASE_URL: 'postgresql://test:test@localhost:5432/test',
    API_VERSION: 'v1',
    CORS_ORIGINS: ['http://localhost:3000'],
    LOG_LEVEL: 'silent',
  }),
  validateEnv: () => ({
    NODE_ENV: 'test',
    PORT: 4001,
    DATABASE_URL: 'postgresql://test:test@localhost:5432/test',
    API_VERSION: 'v1',
    CORS_ORIGINS: ['http://localhost:3000'],
    LOG_LEVEL: 'silent',
  }),
  _resetEnv: vi.fn(),
}));

// ── Mock Prisma — no real DB needed for unit tests ────────────────────────
vi.mock('../src/lib/prisma', () => ({
  prisma: {
    $queryRaw: vi.fn(),
    $disconnect: vi.fn().mockResolvedValue(undefined),
  },
}));

// ── Mock logger to suppress output during tests ───────────────────────────
vi.mock('../src/lib/logger', () => ({
  logger: {
    info: vi.fn(),
    warn: vi.fn(),
    error: vi.fn(),
    fatal: vi.fn(),
    debug: vi.fn(),
    trace: vi.fn(),
  },
  createLogger: vi.fn(),
}));

import { createApp } from '../src/app';
import { prisma } from '../src/lib/prisma';

describe('GET /api/health', () => {
  const app = createApp();

  it('returns 200 with status ok', async () => {
    const res = await request(app).get('/api/health');

    expect(res.status).toBe(200);
    expect(res.body.status).toBe('ok');
    expect(res.body.version).toBeDefined();
    expect(res.body.environment).toBe('test');
    expect(res.body.timestamp).toBeDefined();
    expect(typeof res.body.uptime).toBe('number');
  });

  it('returns X-Request-Id header', async () => {
    const res = await request(app).get('/api/health');
    expect(res.headers['x-request-id']).toBeDefined();
  });

  it('echoes X-Request-Id from request', async () => {
    const customId = 'test-req-id-123';
    const res = await request(app).get('/api/health').set('X-Request-Id', customId);
    expect(res.headers['x-request-id']).toBe(customId);
  });
});

describe('GET /api/health/db', () => {
  const app = createApp();

  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('returns 200 when database is connected', async () => {
    vi.mocked(prisma.$queryRaw).mockResolvedValueOnce([{ '?column?': 1 }]);

    const res = await request(app).get('/api/health/db');

    expect(res.status).toBe(200);
    expect(res.body.status).toBe('ok');
    expect(res.body.database.connected).toBe(true);
    expect(typeof res.body.database.latencyMs).toBe('number');
    expect(res.body.timestamp).toBeDefined();
  });

  it('returns 503 when database is unreachable', async () => {
    vi.mocked(prisma.$queryRaw).mockRejectedValueOnce(
      new Error('connect ECONNREFUSED 127.0.0.1:5432')
    );

    const res = await request(app).get('/api/health/db');

    expect(res.status).toBe(503);
    expect(res.body.status).toBe('error');
    expect(res.body.database.connected).toBe(false);
    expect(res.body.database.message).toBeDefined();
  });
});

describe('GET /api (unknown routes)', () => {
  const app = createApp();

  it('returns 404 for unknown API routes', async () => {
    const res = await request(app).get('/api/unknown-route');
    expect(res.status).toBe(404);
    expect(res.body.success).toBe(false);
    expect(res.body.error.code).toBe('NOT_FOUND');
  });

  it('returns 404 for completely unknown paths', async () => {
    const res = await request(app).get('/some-random-path');
    expect(res.status).toBe(404);
  });
});

describe('Security headers', () => {
  const app = createApp();

  it('sets X-Content-Type-Options header', async () => {
    const res = await request(app).get('/api/health');
    expect(res.headers['x-content-type-options']).toBe('nosniff');
  });

  it('does not expose X-Powered-By', async () => {
    const res = await request(app).get('/api/health');
    expect(res.headers['x-powered-by']).toBeUndefined();
  });
});

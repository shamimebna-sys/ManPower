/**
 * Error handling middleware tests
 *
 * Verifies that AppError, ZodError, and unexpected errors are all
 * converted to consistent ApiErrorResponse shapes.
 */
import { describe, it, expect, vi } from 'vitest';
import request from 'supertest';
import express from 'express';
import { z } from 'zod';
import { AppError, errorHandler } from '../src/middleware/errorHandler';
import { ERROR_CODES } from '@manpower/shared';

// ── Mock environment and logger ───────────────────────────────────────────
vi.mock('../src/config/env', () => ({
  getEnv: () => ({ NODE_ENV: 'test', LOG_LEVEL: 'silent' }),
}));

vi.mock('../src/lib/logger', () => ({
  logger: {
    info: vi.fn(), warn: vi.fn(), error: vi.fn(), fatal: vi.fn(), debug: vi.fn(), trace: vi.fn(),
  },
}));

/** Creates a minimal Express app that throws the given error on GET / */
function makeTestApp(thrower: () => unknown) {
  const app = express();
  app.get('/', (_req, _res, next) => {
    try {
      const result = thrower();
      if (result instanceof Promise) {
        result.catch(next);
      }
    } catch (err) {
      next(err);
    }
  });
  app.use(errorHandler);
  return app;
}

describe('errorHandler — AppError', () => {
  it('handles not found (404)', async () => {
    const app = makeTestApp(() => { throw AppError.notFound('Candidate'); });
    const res = await request(app).get('/');

    expect(res.status).toBe(404);
    expect(res.body.success).toBe(false);
    expect(res.body.error.code).toBe(ERROR_CODES.NOT_FOUND);
    expect(res.body.error.message).toContain('not found');
  });

  it('handles bad request (400)', async () => {
    const app = makeTestApp(() => { throw AppError.badRequest('Invalid input'); });
    const res = await request(app).get('/');

    expect(res.status).toBe(400);
    expect(res.body.error.code).toBe(ERROR_CODES.VALIDATION_ERROR);
  });

  it('handles forbidden (403)', async () => {
    const app = makeTestApp(() => { throw AppError.forbidden(); });
    const res = await request(app).get('/');

    expect(res.status).toBe(403);
    expect(res.body.error.code).toBe(ERROR_CODES.FORBIDDEN);
  });

  it('handles unauthorized (401)', async () => {
    const app = makeTestApp(() => { throw AppError.unauthorized(); });
    const res = await request(app).get('/');

    expect(res.status).toBe(401);
    expect(res.body.error.code).toBe(ERROR_CODES.UNAUTHORIZED);
  });

  it('handles internal error (500)', async () => {
    const app = makeTestApp(() => { throw AppError.internal(); });
    const res = await request(app).get('/');

    expect(res.status).toBe(500);
    expect(res.body.error.code).toBe(ERROR_CODES.INTERNAL_ERROR);
  });
});

describe('errorHandler — ZodError', () => {
  it('handles ZodError as 422 with field details', async () => {
    const schema = z.object({ name: z.string().min(1) });
    const app = makeTestApp(() => {
      schema.parse({ name: '' }); // triggers ZodError
    });

    const res = await request(app).get('/');

    expect(res.status).toBe(422);
    expect(res.body.success).toBe(false);
    expect(res.body.error.code).toBe(ERROR_CODES.VALIDATION_ERROR);
    expect(Array.isArray(res.body.error.details)).toBe(true);
    expect(res.body.error.details[0]).toHaveProperty('field');
    expect(res.body.error.details[0]).toHaveProperty('message');
  });
});

describe('errorHandler — unexpected errors', () => {
  it('handles generic Error as 500', async () => {
    const app = makeTestApp(() => { throw new Error('Something broke'); });
    const res = await request(app).get('/');

    expect(res.status).toBe(500);
    expect(res.body.success).toBe(false);
    expect(res.body.error.code).toBe(ERROR_CODES.INTERNAL_ERROR);
    // In non-production, message is exposed
    expect(res.body.error.message).toContain('Something broke');
  });

  it('handles non-Error throw as 500', async () => {
    const app = makeTestApp(() => { throw 'string error'; });
    const res = await request(app).get('/');

    expect(res.status).toBe(500);
    expect(res.body.success).toBe(false);
  });

  it('response never contains stack traces', async () => {
    const app = makeTestApp(() => { throw new Error('Internal issue'); });
    const res = await request(app).get('/');
    const body = JSON.stringify(res.body);

    expect(body).not.toContain('at Object');
    expect(body).not.toContain('.ts:');
    expect(body).not.toContain('node_modules');
  });
});

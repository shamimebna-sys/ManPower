/**
 * Environment configuration validation tests
 *
 * Verifies that the Zod env schema correctly validates, coerces,
 * and rejects environment configurations.
 */
import { describe, it, expect, afterEach } from 'vitest';
import { validateEnv, _resetEnv } from '../src/config/env';

afterEach(() => {
  _resetEnv();
});

const validBase = {
  NODE_ENV: 'test',
  PORT: '4000',
  DATABASE_URL: 'postgresql://user:pass@localhost:5432/db',
  API_VERSION: 'v1',
  CORS_ORIGINS: 'http://localhost:3000',
  LOG_LEVEL: 'info',
} as const;

describe('validateEnv', () => {
  it('accepts a valid configuration', () => {
    const env = validateEnv(validBase);

    expect(env.NODE_ENV).toBe('test');
    expect(env.PORT).toBe(4000);
    expect(env.DATABASE_URL).toBe('postgresql://user:pass@localhost:5432/db');
    expect(env.API_VERSION).toBe('v1');
    expect(env.LOG_LEVEL).toBe('info');
  });

  it('coerces PORT string to number', () => {
    const env = validateEnv({ ...validBase, PORT: '8080' });
    expect(env.PORT).toBe(8080);
    expect(typeof env.PORT).toBe('number');
  });

  it('splits CORS_ORIGINS on comma', () => {
    const env = validateEnv({
      ...validBase,
      CORS_ORIGINS: 'http://localhost:3000,https://app.example.com',
    });
    expect(env.CORS_ORIGINS).toEqual(['http://localhost:3000', 'https://app.example.com']);
  });

  it('uses default PORT=4000 when not set', () => {
    const env = validateEnv({ ...validBase, PORT: undefined });
    expect(env.PORT).toBe(4000);
  });

  it('uses default NODE_ENV=development when not set', () => {
    const env = validateEnv({ ...validBase, NODE_ENV: undefined });
    expect(env.NODE_ENV).toBe('development');
  });

  it('throws when DATABASE_URL is missing', () => {
    expect(() =>
      validateEnv({ ...validBase, DATABASE_URL: undefined })
    ).toThrow('Environment validation failed');
  });

  it('throws when DATABASE_URL is not a postgresql:// URL', () => {
    expect(() =>
      validateEnv({ ...validBase, DATABASE_URL: 'mysql://user:pass@localhost/db' })
    ).toThrow('Environment validation failed');
  });

  it('throws when NODE_ENV is an invalid value', () => {
    expect(() =>
      validateEnv({ ...validBase, NODE_ENV: 'staging' as never })
    ).toThrow('Environment validation failed');
  });

  it('throws when PORT is out of range', () => {
    expect(() =>
      validateEnv({ ...validBase, PORT: '99999' })
    ).toThrow('Environment validation failed');
  });

  it('throws when LOG_LEVEL is invalid', () => {
    expect(() =>
      validateEnv({ ...validBase, LOG_LEVEL: 'verbose' as never })
    ).toThrow('Environment validation failed');
  });
});

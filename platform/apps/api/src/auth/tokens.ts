import { createHash, randomBytes, timingSafeEqual } from 'node:crypto';

export const SESSION_COOKIE = 'manpower_session';
export const CSRF_COOKIE = 'manpower_csrf';

export function createSecureToken(bytes = 32): string {
  return randomBytes(bytes).toString('base64url');
}

export function hashToken(token: string): string {
  return createHash('sha256').update(token, 'utf8').digest('hex');
}

export function safeTokenMatches(rawToken: string, expectedHash: string): boolean {
  const actual = Buffer.from(hashToken(rawToken), 'hex');
  const expected = Buffer.from(expectedHash, 'hex');
  return actual.length === expected.length && timingSafeEqual(actual, expected);
}

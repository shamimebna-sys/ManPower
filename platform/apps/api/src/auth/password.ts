import { compare, hash } from 'bcryptjs';
import { PasswordSchema } from '@manpower/shared';
import { getEnv } from '../config/env.js';

// A fixed valid bcrypt hash is evaluated for unknown users so login timing does
// not reveal whether an identifier exists.
const DUMMY_HASH = '$2b$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxrE0EYvF7t2XU0QWgT6YqZ4V6K';

export function validateNewPassword(password: string): void {
  PasswordSchema.parse(password);
}

export async function hashPassword(password: string): Promise<string> {
  validateNewPassword(password);
  return hash(password, getEnv().BCRYPT_ROUNDS);
}

export async function verifyPassword(password: string, passwordHash?: string): Promise<boolean> {
  return compare(password, passwordHash ?? DUMMY_HASH);
}

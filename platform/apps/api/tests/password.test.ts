import { describe, expect, it, vi } from 'vitest';

vi.mock('../src/config/env', () => ({
  getEnv: () => ({ BCRYPT_ROUNDS: 10 }),
}));

import { hashPassword, verifyPassword } from '../src/auth/password';

describe('password security', () => {
  it('hashes with bcrypt and verifies without exposing the plaintext', async () => {
    const plaintext = 'UniqueStrong!Password42';
    const passwordHash = await hashPassword(plaintext);
    expect(passwordHash).toMatch(/^\$2[aby]\$/);
    expect(passwordHash).not.toContain(plaintext);
    expect(await verifyPassword(plaintext, passwordHash)).toBe(true);
    expect(await verifyPassword('WrongPassword!42', passwordHash)).toBe(false);
  });

  it('rejects weak passwords', async () => {
    await expect(hashPassword('password')).rejects.toThrow();
    await expect(hashPassword('admin123')).rejects.toThrow();
  });

  it('performs a safe dummy comparison for unknown users', async () => {
    await expect(verifyPassword('AnyPassword!42')).resolves.toBe(false);
  });
});

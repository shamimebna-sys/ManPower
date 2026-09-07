import type { AuthenticatedUser } from '@manpower/shared';

export function hasPermission(user: AuthenticatedUser | null | undefined, key: string): boolean {
  if (!user) return false;
  return user.roles.includes('super_admin') || user.permissions.includes(key);
}

export function hasAnyPermission(user: AuthenticatedUser | null | undefined, keys: readonly string[]): boolean {
  return keys.some((key) => hasPermission(user, key));
}

export const OVERSEAS_READ_KEYS = [
  'overseas.medical.read',
  'overseas.police_clearance.read',
  'overseas.arc.read',
  'overseas.labour_contract.read',
  'overseas.visa.read',
  'overseas.flight.read',
] as const;

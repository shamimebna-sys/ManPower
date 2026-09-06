import type { AuthenticatedUser } from '@manpower/shared';

export function hasPermission(user: AuthenticatedUser | null | undefined, key: string): boolean {
  if (!user) return false;
  return user.roles.includes('super_admin') || user.permissions.includes(key);
}

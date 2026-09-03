import type { AuthenticatedUser } from '@manpower/shared';

interface UserWithRoles {
  id: string;
  email: string;
  username: string | null;
  displayName: string;
  status: 'ACTIVE' | 'INACTIVE' | 'SUSPENDED';
  roles: Array<{
    role: {
      key: string;
      permissions: Array<{ permission: { key: string } }>;
    };
  }>;
}

export function toAuthenticatedUser(user: UserWithRoles): AuthenticatedUser {
  const roles = user.roles.map(({ role }) => role.key);
  const permissions = new Set(
    user.roles.flatMap(({ role }) => role.permissions.map(({ permission }) => permission.key))
  );

  return {
    id: user.id,
    email: user.email,
    username: user.username,
    displayName: user.displayName,
    status: user.status,
    roles,
    permissions: [...permissions].sort(),
  };
}

export const authUserInclude = {
  roles: {
    include: {
      role: {
        include: {
          permissions: {
            include: { permission: true },
          },
        },
      },
    },
  },
} as const;

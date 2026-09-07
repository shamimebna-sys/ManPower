import { prisma } from '../lib/prisma.js';
import {
  APPROVED_ROLES,
  FOUNDATION_PERMISSIONS,
  M4_ROLE_GRANTS,
  M5_ROLE_GRANTS,
} from '../iam/permission-catalogue.js';

/**
 * Upserts the permission catalogue, approved roles, super_admin grants,
 * and the explicit M4 role grants. IAM keys stay on super_admin only.
 */
async function main(): Promise<void> {
  const permissions = await Promise.all(
    FOUNDATION_PERMISSIONS.map(([key, name]) =>
      prisma.permission.upsert({
        where: { key },
        create: { key, name },
        update: { name },
      })
    )
  );
  const permissionByKey = new Map(permissions.map((permission) => [permission.key, permission]));

  const roles = await Promise.all(
    APPROVED_ROLES.map(([key, name, description]) =>
      prisma.role.upsert({
        where: { key },
        create: {
          key,
          name,
          description,
          isSystem: key === 'super_admin',
        },
        update: { name, description },
      })
    )
  );
  const roleByKey = new Map(roles.map((role) => [role.key, role]));

  const superAdmin = roleByKey.get('super_admin');
  if (!superAdmin) {
    throw new Error('super_admin role is missing.');
  }

  await Promise.all(
    permissions.map((permission) =>
      prisma.rolePermission.upsert({
        where: {
          roleId_permissionId: { roleId: superAdmin.id, permissionId: permission.id },
        },
        create: { roleId: superAdmin.id, permissionId: permission.id },
        update: {},
      })
    )
  );

  for (const grants of [M4_ROLE_GRANTS, M5_ROLE_GRANTS]) {
    for (const [roleKey, keys] of Object.entries(grants)) {
      const role = roleByKey.get(roleKey);
      if (!role) {
        throw new Error(`Approved role ${roleKey} is missing.`);
      }
      await Promise.all(
        keys.map((permissionKey) => {
          const permission = permissionByKey.get(permissionKey);
          if (!permission) {
            throw new Error(`Permission ${permissionKey} is missing.`);
          }
          return prisma.rolePermission.upsert({
            where: {
              roleId_permissionId: { roleId: role.id, permissionId: permission.id },
            },
            create: { roleId: role.id, permissionId: permission.id },
            update: {},
          });
        })
      );
    }
  }

  console.log(
    `Ensured ${permissions.length} permissions, ${roles.length} roles, and M4+M5 grants.`
  );
}

main()
  .catch((error: unknown) => {
    console.error(error);
    process.exitCode = 1;
  })
  .finally(async () => {
    await prisma.$disconnect();
  });

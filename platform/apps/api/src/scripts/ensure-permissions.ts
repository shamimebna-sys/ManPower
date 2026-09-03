import { prisma } from '../lib/prisma.js';
import { FOUNDATION_PERMISSIONS } from '../iam/permission-catalogue.js';

/**
 * Upserts the current permission catalogue and grants any missing keys to
 * super_admin only. Other role grants remain A07-gated and are not invented here.
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

  const superAdmin = await prisma.role.findUnique({ where: { key: 'super_admin' } });
  if (!superAdmin) {
    throw new Error('super_admin role is missing. Run db:bootstrap-admin first.');
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

  console.log(`Ensured ${permissions.length} permissions on super_admin.`);
}

main()
  .catch((error: unknown) => {
    console.error(error);
    process.exitCode = 1;
  })
  .finally(async () => {
    await prisma.$disconnect();
  });

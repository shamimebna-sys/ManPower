import { PasswordSchema } from '@manpower/shared';
import { prisma } from '../lib/prisma.js';
import { getEnv } from '../config/env.js';
import { hashPassword } from '../auth/password.js';
import { AUDIT_EVENTS, writeAuditEvent } from '../audit/audit.js';
import { FOUNDATION_PERMISSIONS } from '../iam/permission-catalogue.js';

async function main(): Promise<void> {
  const env = getEnv();
  const email = env.BOOTSTRAP_ADMIN_EMAIL?.trim().toLowerCase();
  const username = env.BOOTSTRAP_ADMIN_USERNAME?.trim().toLowerCase();
  const password = env.BOOTSTRAP_ADMIN_PASSWORD;

  if (!email || !username || !password) {
    throw new Error(
      'Set BOOTSTRAP_ADMIN_EMAIL, BOOTSTRAP_ADMIN_USERNAME, and ' +
        'BOOTSTRAP_ADMIN_PASSWORD before running db:bootstrap-admin.'
    );
  }
  PasswordSchema.parse(password);

  const existing = await prisma.user.findFirst({
    where: { OR: [{ email }, { username }] },
    select: { id: true },
  });
  if (existing) {
    throw new Error('Bootstrap refused: a user with that email or username already exists.');
  }

  const passwordHash = await hashPassword(password);
  await prisma.$transaction(async (tx) => {
    const permissions = await Promise.all(
      FOUNDATION_PERMISSIONS.map(([key, name]) =>
        tx.permission.upsert({
          where: { key },
          create: { key, name },
          update: { name },
        })
      )
    );
    const role = await tx.role.upsert({
      where: { key: 'super_admin' },
      create: {
        key: 'super_admin',
        name: 'Super Administrator',
        description: 'Unrestricted platform administrator. Assign sparingly.',
        isSystem: true,
      },
      update: {},
    });
    const user = await tx.user.create({
      data: {
        email,
        username,
        displayName: 'Super Administrator',
        passwordHash,
        status: 'ACTIVE',
      },
    });
    await tx.userRole.create({
      data: { userId: user.id, roleId: role.id, assignedBy: user.id },
    });
    await Promise.all(
      permissions.map((permission) =>
        tx.rolePermission.upsert({
          where: {
            roleId_permissionId: { roleId: role.id, permissionId: permission.id },
          },
          create: {
            roleId: role.id,
            permissionId: permission.id,
            grantedBy: user.id,
          },
          update: {},
        })
      )
    );
    await writeAuditEvent(
      {
        eventType: AUDIT_EVENTS.ROLE_ASSIGNED,
        actorUserId: user.id,
        targetType: 'user',
        targetId: user.id,
        metadata: { roleKey: 'super_admin', source: 'secure_bootstrap' },
      },
      tx
    );
  });

  console.log(`Super administrator created for ${email}.`);
  console.log('Remove BOOTSTRAP_ADMIN_PASSWORD from the environment now.');
}

main()
  .catch((error: unknown) => {
    console.error(error instanceof Error ? error.message : 'Bootstrap failed');
    process.exitCode = 1;
  })
  .finally(async () => {
    await prisma.$disconnect();
  });

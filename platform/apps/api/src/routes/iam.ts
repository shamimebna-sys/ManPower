import { Router } from 'express';
import type { Router as ExpressRouter, Request, Response } from 'express';
import { z } from 'zod';
import { AssignRoleSchema, CreateUserSchema, GrantPermissionSchema, UserBindingSchema } from '@manpower/shared';
import type { ApiResponse, MessageResult, UserBindings } from '@manpower/shared';
import { prisma } from '../lib/prisma.js';
import { requireAuth, requireCsrf, requirePermission } from '../middleware/auth.js';
import { AppError } from '../middleware/errorHandler.js';
import { AUDIT_EVENTS, writeAuditEvent } from '../audit/audit.js';
import { hashPassword } from '../auth/password.js';
import {
  assertBindingTargetExists,
  assertExclusiveBinding,
  bindingUpdate,
  toUserBindings,
} from '../recruitment/bindings.js';

export const iamRouter: ExpressRouter = Router();

const IdParams = z.object({ userId: z.string().uuid() });
const RoleParams = z.object({ roleId: z.string().uuid() });
const StatusSchema = z.object({ status: z.enum(['ACTIVE', 'INACTIVE', 'SUSPENDED']) });

iamRouter.use(requireAuth, requireCsrf);

iamRouter.get('/users', requirePermission('iam.user.read'), async (_req, res: Response) => {
  const users = await prisma.user.findMany({
    select: {
      id: true,
      email: true,
      username: true,
      displayName: true,
      status: true,
      createdAt: true,
      roles: { select: { role: { select: { key: true } } } },
    },
    orderBy: { createdAt: 'desc' },
    take: 100,
  });
  const body: ApiResponse<typeof users> = { success: true, data: users };
  res.status(200).json(body);
});

iamRouter.post(
  '/users',
  requirePermission('iam.user.manage'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const actorUserId = req.auth.user.id;
    const input = CreateUserSchema.parse(req.body);
    const passwordHash = await hashPassword(input.password);
    try {
      const user = await prisma.$transaction(async (tx) => {
        const created = await tx.user.create({
          data: {
            email: input.email,
            ...(input.username ? { username: input.username } : {}),
            displayName: input.displayName,
            passwordHash,
            status: input.status,
          },
          select: {
            id: true,
            email: true,
            username: true,
            displayName: true,
            status: true,
            createdAt: true,
          },
        });
        await writeAuditEvent(
          {
            eventType: AUDIT_EVENTS.ACCOUNT_CREATED,
            actorUserId,
            targetType: 'user',
            targetId: created.id,
            metadata: { initialStatus: input.status },
            request: req,
          },
          tx
        );
        return created;
      });
      const body: ApiResponse<typeof user> = { success: true, data: user };
      res.status(201).json(body);
    } catch (error) {
      if (
        typeof error === 'object' &&
        error !== null &&
        'code' in error &&
        error.code === 'P2002'
      ) {
        throw AppError.conflict('Email or username is already in use');
      }
      throw error;
    }
  }
);

iamRouter.post(
  '/users/:userId/roles',
  requirePermission('iam.user_role.manage'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const actorUserId = req.auth.user.id;
    const { userId } = IdParams.parse(req.params);
    const { roleKey } = AssignRoleSchema.parse(req.body);
    const role = await prisma.role.findUnique({ where: { key: roleKey } });
    if (!role) throw AppError.notFound('Role');

    await prisma.$transaction(async (tx) => {
      await tx.userRole.upsert({
        where: { userId_roleId: { userId, roleId: role.id } },
        create: { userId, roleId: role.id, assignedBy: actorUserId },
        update: {},
      });
      await writeAuditEvent(
        {
          eventType: AUDIT_EVENTS.ROLE_ASSIGNED,
          actorUserId,
          targetType: 'user',
          targetId: userId,
          metadata: { roleKey },
          request: req,
        },
        tx
      );
    });

    const body: ApiResponse<MessageResult> = {
      success: true,
      data: { message: `Role ${roleKey} assigned` },
    };
    res.status(200).json(body);
  }
);

iamRouter.post(
  '/roles/:roleId/permissions',
  requirePermission('iam.role_permission.manage'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const actorUserId = req.auth.user.id;
    const { roleId } = RoleParams.parse(req.params);
    const { permissionKey } = GrantPermissionSchema.parse(req.body);
    const permission = await prisma.permission.findUnique({ where: { key: permissionKey } });
    if (!permission) throw AppError.notFound('Permission');

    await prisma.$transaction(async (tx) => {
      await tx.rolePermission.upsert({
        where: { roleId_permissionId: { roleId, permissionId: permission.id } },
        create: { roleId, permissionId: permission.id, grantedBy: actorUserId },
        update: {},
      });
      await writeAuditEvent(
        {
          eventType: AUDIT_EVENTS.PERMISSION_GRANTED,
          actorUserId,
          targetType: 'role',
          targetId: roleId,
          metadata: { permissionKey },
          request: req,
        },
        tx
      );
    });

    const body: ApiResponse<MessageResult> = {
      success: true,
      data: { message: `Permission ${permissionKey} granted` },
    };
    res.status(200).json(body);
  }
);

iamRouter.patch(
  '/users/:userId/status',
  requirePermission('iam.user.manage'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const actorUserId = req.auth.user.id;
    const { userId } = IdParams.parse(req.params);
    const { status } = StatusSchema.parse(req.body);

    await prisma.$transaction(async (tx) => {
      const previous = await tx.user.findUnique({
        where: { id: userId },
        select: { status: true },
      });
      if (!previous) throw AppError.notFound('User');
      await tx.user.update({ where: { id: userId }, data: { status } });
      if (status !== 'ACTIVE') {
        await tx.session.updateMany({
          where: { userId, revokedAt: null },
          data: { revokedAt: new Date() },
        });
      }
      await writeAuditEvent(
        {
          eventType: AUDIT_EVENTS.ACCOUNT_STATUS_CHANGED,
          actorUserId,
          targetType: 'user',
          targetId: userId,
          metadata: { from: previous.status, to: status },
          request: req,
        },
        tx
      );
    });

    const body: ApiResponse<MessageResult> = {
      success: true,
      data: { message: `Account status changed to ${status}` },
    };
    res.status(200).json(body);
  }
);

iamRouter.put(
  '/users/:userId/bindings',
  requirePermission('partners.user_binding.manage'),
  async (req: Request, res: Response) => {
    if (!req.auth) throw AppError.unauthorized();
    const { userId } = IdParams.parse(req.params);
    const input = UserBindingSchema.parse(req.body);
    const updated = await prisma.$transaction(async (tx) => {
      const current = await tx.user.findUnique({
        where: { id: userId },
        select: {
          id: true,
          agentId: true,
          subAgentId: true,
          agencierId: true,
          companierId: true,
          candidateId: true,
          employerId: true,
          teacherId: true,
        },
      });
      if (!current) throw AppError.notFound('User');
      const bindings = toUserBindings(current);
      if (input.targetId) {
        assertExclusiveBinding(bindings, input.domain, input.targetId);
        await assertBindingTargetExists(tx, input.domain, input.targetId);
      }
      const user = await tx.user.update({
        where: { id: userId },
        data: bindingUpdate(input.domain, input.targetId),
        select: {
          agentId: true,
          subAgentId: true,
          agencierId: true,
          companierId: true,
          candidateId: true,
          employerId: true,
          teacherId: true,
        },
      });
      const teacherBind = input.domain === 'teacher';
      await writeAuditEvent(
        {
          eventType: teacherBind
            ? input.targetId
              ? AUDIT_EVENTS.TRAINING_TEACHER_BOUND
              : AUDIT_EVENTS.TRAINING_TEACHER_UNBOUND
            : input.targetId
              ? AUDIT_EVENTS.USER_PARTNER_BOUND
              : AUDIT_EVENTS.USER_PARTNER_UNBOUND,
          actorUserId: req.auth?.user.id,
          targetType: 'user',
          targetId: userId,
          metadata: { domain: input.domain, bound: Boolean(input.targetId) },
          request: req,
        },
        tx
      );
      return toUserBindings(user);
    });
    const body: ApiResponse<UserBindings> = { success: true, data: updated };
    res.status(200).json(body);
  }
);

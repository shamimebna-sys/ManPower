import { beforeEach, describe, expect, it, vi } from 'vitest';

const create = vi.hoisted(() => vi.fn());
vi.mock('../src/lib/prisma', () => ({
  prisma: { auditEvent: { create } },
}));

import { AUDIT_EVENTS, writeAuditEvent } from '../src/audit/audit';

describe('append-only audit writer', () => {
  beforeEach(() => create.mockReset());

  it('creates a security event with correlation metadata', async () => {
    create.mockResolvedValue({});
    await writeAuditEvent({
      eventType: AUDIT_EVENTS.PERMISSION_GRANTED,
      actorUserId: 'actor-id',
      targetType: 'role',
      targetId: 'role-id',
      metadata: { permissionKey: 'iam.user.read' },
    });
    expect(create).toHaveBeenCalledWith({
      data: expect.objectContaining({
        eventType: AUDIT_EVENTS.PERMISSION_GRANTED,
        actorUserId: 'actor-id',
        metadata: { permissionKey: 'iam.user.read' },
      }),
    });
  });

  it('removes password, token, secret, cookie, nid, and passport metadata keys', async () => {
    create.mockResolvedValue({});
    await writeAuditEvent({
      eventType: AUDIT_EVENTS.LOGIN_FAILURE,
      metadata: {
        reason: 'invalid_credentials',
        password: 'must-not-appear',
        accessToken: 'must-not-appear',
        clientSecret: 'must-not-appear',
        cookie: 'must-not-appear',
        nid: 'must-not-appear',
        passportNo: 'must-not-appear',
      },
    });
    const serialized = JSON.stringify(create.mock.calls);
    expect(serialized).toContain('invalid_credentials');
    expect(serialized).not.toContain('must-not-appear');
  });
});

import { z } from 'zod';

export const PasswordSchema = z
  .string()
  .min(12, 'Password must contain at least 12 characters')
  .max(128, 'Password must not exceed 128 characters')
  .regex(/[a-z]/, 'Password must contain a lowercase letter')
  .regex(/[A-Z]/, 'Password must contain an uppercase letter')
  .regex(/[0-9]/, 'Password must contain a number')
  .regex(/[^A-Za-z0-9]/, 'Password must contain a symbol');

export const LoginSchema = z.object({
  identifier: z.string().trim().min(1).max(320),
  password: z.string().min(1).max(128),
});

export const ChangePasswordSchema = z
  .object({
    currentPassword: z.string().min(1).max(128),
    newPassword: PasswordSchema,
  })
  .refine((value) => value.currentPassword !== value.newPassword, {
    message: 'New password must be different from the current password',
    path: ['newPassword'],
  });

export const AssignRoleSchema = z.object({
  roleKey: z.string().trim().regex(/^[a-z][a-z0-9_.-]{1,99}$/),
});

export const GrantPermissionSchema = z.object({
  permissionKey: z.string().trim().regex(/^[a-z][a-z0-9_.-]{1,149}$/),
});

export const CreateUserSchema = z.object({
  email: z.string().trim().email().max(320).transform((value) => value.toLowerCase()),
  username: z
    .string()
    .trim()
    .min(3)
    .max(100)
    .regex(/^[a-zA-Z][a-zA-Z0-9_.-]*$/)
    .transform((value) => value.toLowerCase())
    .optional(),
  displayName: z.string().trim().min(1).max(200),
  password: PasswordSchema,
  status: z.enum(['ACTIVE', 'INACTIVE', 'SUSPENDED']).default('ACTIVE'),
});

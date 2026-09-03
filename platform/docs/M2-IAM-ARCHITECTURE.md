# M2 IAM, RBAC, and Audit Architecture

## Scope

M2 implements only identity and security infrastructure. No candidate, recruitment,
training, finance, reporting, notification, migration, or other business model exists in
the new database.

## Authentication

- Users sign in with a normalized email address or username.
- Passwords use bcrypt with a configurable cost (default 12) and a 12-character complexity
  policy. Unknown users execute a dummy bcrypt comparison to reduce identifier timing leaks.
- Sessions are opaque random 256-bit tokens. Only SHA-256 token hashes are stored in
  PostgreSQL. The raw token is delivered in a `Secure` (production), `HttpOnly`,
  `SameSite=Strict` cookie.
- State-changing authenticated requests require a double-submit CSRF token. Its hash is
  stored with the session.
- Logout revokes the current session. Password change and account deactivation revoke all
  active sessions.
- Login is rate-limited to 10 attempts per IP per 15 minutes.

## RBAC

Authorization uses stable string keys. Numeric legacy role IDs are never runtime
authorization identifiers.

M2 seeds only five IAM foundation permissions:

- `iam.user.read`
- `iam.user.manage`
- `iam.user_role.manage`
- `iam.role_permission.manage`
- `iam.audit.read`

**A07 is DECISION LOCKED.** Approved role keys and candidate row-scopes are in
`docs/PRE-M4-HARD-GATE.md`. Report/document keys are still not defined.
M2–M4 grant the foundation catalogue to `super_admin` only. Other roles are not seeded.

The approved legacy permission catalog will be loaded later under its migration gate.
Backend middleware evaluates permissions on every protected API call. Frontend visibility
is only a usability feature and is never an authorization boundary.

## Audit

Security events are written to `audit.audit_events`. Normal APIs expose no update/delete
operation. PostgreSQL triggers reject every update or delete at database level, making the
table append-only even when application code is defective.

Audit metadata is allow-shaped and automatically removes keys containing `password`,
`token`, `secret`, `authorization`, or `cookie`. Passwords, hashes, session tokens, and CSRF
tokens are never audit values.

## Initial administrator

1. Start PostgreSQL and apply migrations.
2. Set `BOOTSTRAP_ADMIN_EMAIL`, `BOOTSTRAP_ADMIN_USERNAME`, and a unique strong
   `BOOTSTRAP_ADMIN_PASSWORD` in the local process environment.
3. Run `pnpm db:bootstrap-admin`.
4. Remove the password variable immediately.

The command refuses weak passwords and refuses to overwrite an existing user. No default
administrator password exists.

# Authentication

## User fields
- `username` (unique, login identifier #1)
- `phone` (unique, login identifier #2, E.164)
- `email` (unique, verification/reset)
- `password` (argon2id/bcrypt, NEVER plaintext)
- `email_verified_at`, `phone_verified_at`
- timestamps + `deleted_at` (soft delete)

## Password rules (VERY STRONG)
- Minimum 12 characters; required: uppercase, lowercase, digit, symbol.
- Must not contain username/email/phone.
- Confirmation required (`password_confirmation` must match).
- Argon2id default Laravel cost.

## Flow
- Login: username OR phone + password.
- Lockout: 5 consecutive fails → 15m lock (throttle key = ip+identifier). Fails & locks → audit.
- Rate limit: /login, /password/*, /register (e.g. 10/15m).
- Reset: hashed token, 60m expiry, single-use.
- Verify: email activation before full access.
- Logout: invalidate session + Sanctum token; audit.
- Change password: old password required; audit; revoke other sessions.

## Self-service
- Profile: name, avatar, phone; change password; view active sessions.
- Session mgmt: list devices, logout all other devices.

## Security gate (must be green)
- Boundary validation; parameterized SQL; output escaped.
- Cookie: httpOnly, secure, sameSite=lax.
- No secrets in code/VCS (.env gitignored).
- Rate limit active on auth endpoints.

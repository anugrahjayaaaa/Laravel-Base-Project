# Observability (Logger & Monitoring)

Goal: fast root cause when an issue/bug happens in prod.

## Logs (structured)
- `Log::info/error` with context array (not string concat).
- `daily` channel in prod; never log secrets/PII.
- Exception handler → log + Sentry (stack + release + user tag).

## Error tracking — Sentry
- Init in bootstrap; `release = app.version`; small `traces_sample_rate` (0.2).
- Tag `user_id` to filter per user.
- Alerts: error-rate spike, /up failing, queue backlog.

## Health check
- `/up` route (Laravel health) checks DB + cache + queue. Used by LB/uptime.

## DB monitoring
- Slow query log + performance_schema (see `mysql` skill).
- Alert when p95 exceeds threshold.

## Gate
- Test exception in staging lands in Sentry with release tag.
- /up returns 200 + DB/cache checks green.
- Logs do not leak secrets.

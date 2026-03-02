# Security Policy

## Supported Versions

| Version | Supported |
|---------|-----------|
| 0.1.x   | Active    |

---

## Reporting a Vulnerability

**Do not open a public GitHub issue for security vulnerabilities.**

Report privately via GitHub Security Advisories:

**[→ Report a vulnerability](https://github.com/OmDongaonkar03/micrologs-laravel/security/advisories/new)**

You will receive a response within 72 hours.

---

## Security Design

**The secret key is never exposed.** It is stored in Laravel's config system (`config('micrologs.key')`) which reads from `.env`. It is only ever sent as a request header to your own Micrologs server - never logged, never returned in responses.

**The SDK never throws.** All failures are routed through Laravel's `report()` function. This means failures appear in your Laravel log without ever crashing the application or leaking stack traces to end users.

**The TrackErrors middleware always re-throws.** After tracking an exception, the middleware re-throws it so Laravel's own exception handler runs normally. The middleware never swallows exceptions.

**Context data in TrackErrors is minimal.** The middleware sends method, URL, IP, user agent, and authenticated user ID - nothing from the request body. Sensitive form data, passwords, and tokens are never sent.

**5 second timeout.** All HTTP calls have a hard configurable timeout (default: 5 seconds via `MICROLOGS_TIMEOUT`). A hung Micrologs server will not hang your application.
# Changelog

All notable changes to `micrologs/laravel` will be documented here.

---

## [1.0.0] - 2026-03-03

Stable release. No code changes from `0.1.0` - version bumped to reflect stability.

### Requires
- Micrologs engine v1.3.0+

---

## [0.1.0] - 2026-03-02

Initial release.

### Added
- `Micrologs::error()` - track errors from any Laravel controller, job, or service
- `Micrologs::audit()` - track audit events
- `Micrologs::createLink()` - create tracked short links
- `Micrologs::getLink()` - fetch a single link by code
- `Micrologs::editLink()` - edit a link's destination, label, or active state
- `Micrologs::deleteLink()` - delete tracked links
- `Micrologs::updateErrorStatus()` - update error group status individually or in bulk
- `Micrologs::verify()` - verify a public or secret key
- `Micrologs::analytics()` - full analytics query surface: visitors, returning, sessions, pages, devices, locations, referrers, utm, errors, errorsTrend, errorDetail, audits, links, linkDetail
- `TrackErrors` middleware - automatically captures unhandled exceptions and sends them to Micrologs with request context and inferred severity. Compatible with Laravel 10, 11, and 12.
- `MicrologsServiceProvider` - registers client as singleton, publishes config
- `Micrologs` facade - full IDE autocomplete via `@method` docblocks
- Config file - `host`, `key`, `timeout` via `.env`
- Auto-discovery - no manual registration in `config/app.php` required
- Silent failure - all methods return `null` on error, failures routed through Laravel's `report()` so they appear in your log without crashing the app
- Configurable timeout via `MICROLOGS_TIMEOUT` env variable (default: 5 seconds)
- `environment` auto-detected from `app()->environment()` when not explicitly passed to `error()`
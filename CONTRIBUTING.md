# Contributing to micrologs/laravel

Thanks for your interest. Before starting any work, open an issue first - PRs without prior discussion may be declined if they conflict with planned direction.

---

## Reporting a bug

Open an issue and include:
- What you called
- What you expected back
- What actually happened
- Laravel version, PHP version
- Micrologs engine version you're running against

---

## Submitting a PR

1. Open an issue and discuss the change first
2. Fork the repo and create a branch from `main`
3. Make your changes
4. Test against a real running Micrologs server (v1.3.0+)
5. Open a PR with a clear description of what changed and why

---

## Local setup

```bash
git clone https://github.com/OmDongaonkar03/micrologs-laravel.git
cd micrologs-laravel
composer install
```

---

## Code style

- Follow PSR-12
- All public methods must return `?array` - never throw
- Route failures through Laravel's `report()` - never `throw` or `echo`
- Use Laravel's HTTP client (`Illuminate\Http\Client\Factory`) - not raw curl or file_get_contents
- Maintain support for Laravel 10, 11, and 12 - check before using new APIs
- Keep `environment` auto-detected from `app()->environment()` as the default

---

## What we won't merge

- Any external dependency beyond what Laravel already provides
- Breaking changes to existing method signatures without a major version bump
- Methods that don't correspond to actual Micrologs engine endpoints
- PRs that drop Laravel 10 support
- PRs that haven't been discussed in an issue first
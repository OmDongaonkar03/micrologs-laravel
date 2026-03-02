# micrologs/laravel

[![Packagist Version](https://img.shields.io/packagist/v/micrologs/laravel)](https://packagist.org/packages/micrologs/laravel)

Laravel SDK for [Micrologs](https://github.com/OmDongaonkar03/Micrologs) - self-hosted analytics and error tracking.

**Requires Laravel 10, 11, or 12. PHP 8.1+.**

---

## How it works

Micrologs is an engine you install on your own server. You own the database, you own the data. This SDK is a first-class Laravel package - it registers a service provider, a facade, and optional middleware. HTTP calls go to your server, not to any third-party service.

```
Your Laravel app  →  SDK  →  your Micrologs server  →  your database
```

Nothing goes anywhere you don't control.

---

## Install

```bash
composer require micrologs/laravel
```

Auto-discovery registers the service provider and facade automatically. No manual registration needed.

---

## Configure

Publish the config file:

```bash
php artisan vendor:publish --tag=micrologs-config
```

Add to your `.env`:

```env
MICROLOGS_HOST=https://analytics.yourdomain.com
MICROLOGS_KEY=your_secret_key
```

`MICROLOGS_HOST` is the URL of the server where you installed Micrologs. `MICROLOGS_KEY` is your project secret key - find it when you create a project. Never use the public key here - that is for the JS snippet only.

---

## Usage

### Via facade

```php
use Micrologs\Laravel\Facades\Micrologs;

Micrologs::error('Payment failed', ['severity' => 'critical']);
Micrologs::audit('order.placed', $user->email, ['order_id' => $order->id]);
```

### Via injection

```php
use Micrologs\Laravel\MicrologsClient;

class CheckoutController extends Controller
{
    public function store(Request $request, MicrologsClient $micrologs)
    {
        try {
            // process order
        } catch (\Exception $e) {
            $micrologs->error($e->getMessage(), [
                'type'    => 'CheckoutError',
                'severity' => 'critical',
                'context' => ['order_id' => $request->order_id],
            ]);

            throw $e;
        }
    }
}
```

---

## Automatic error tracking

The `TrackErrors` middleware automatically captures unhandled exceptions and sends them to Micrologs. Register it globally so every unhandled exception is tracked without any manual `try/catch`.

**Laravel 11+ (`bootstrap/app.php`):**

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\Micrologs\Laravel\Middleware\TrackErrors::class);
})
```

**Laravel 10 (`app/Http/Kernel.php`):**

```php
protected $middleware = [
    \Micrologs\Laravel\Middleware\TrackErrors::class,
];
```

The middleware automatically includes the request method, URL, authenticated user ID, and IP in the error context. Severity is inferred from the exception type - `Error` → `critical`, auth exceptions → `warning`, validation exceptions → `info`, everything else → `error`. The exception is always re-thrown after tracking so Laravel's own error handling is unaffected.

---

## Tracking

### Track an error manually

```php
Micrologs::error('Something went wrong', [
    'type'        => 'PaymentError',           // groups errors of same type together
    'severity'    => 'critical',               // info | warning | error | critical
    'file'        => __FILE__,
    'line'        => __LINE__,
    'stack'       => $e->getTraceAsString(),
    'url'         => request()->fullUrl(),
    'environment' => app()->environment(),     // auto-detected if omitted
    'context'     => ['order_id' => 123],      // any extra data, capped at 8KB
]);
```

All fields except `message` are optional. `environment` defaults to `app()->environment()` if not provided.

**How grouping works:** Micrologs hashes `type + message + file + line` into a fingerprint. The same error firing 1000 times creates 1 group with 1000 occurrences. If a resolved error fires again, it automatically reopens.

---

### Track an audit event

```php
// action (required), actor (optional), context (optional)
Micrologs::audit('user.login',       $user->email, ['role' => 'admin', 'ip' => request()->ip()]);
Micrologs::audit('order.placed',     $user->email, ['order_id' => $order->id, 'amount' => 2999]);
Micrologs::audit('settings.updated', $user->email);
Micrologs::audit('api_key.rotated',  'admin@yourdomain.com');
```

Use dot notation for `action` by convention (`resource.action`) - it makes filtering easy.

---

## Link management

### Create a tracked short link

```php
$result = Micrologs::createLink('https://yourdomain.com/pricing', 'Pricing CTA');

// $result['data']:
// [
//     'code'            => 'aB3xYz12',
//     'short_url'       => 'https://analytics.yourdomain.com/api/redirect.php?c=aB3xYz12',
//     'destination_url' => 'https://yourdomain.com/pricing',
//     'label'           => 'Pricing CTA',
// ]
```

### Get a single link

```php
$result = Micrologs::getLink('aB3xYz12');
// Returns link details including total_clicks
```

### Edit a link

```php
// Any combination of fields - all optional except code
Micrologs::editLink('aB3xYz12', [
    'destination_url' => 'https://yourdomain.com/new-page',
    'label'           => 'Updated CTA',
    'is_active'       => false,
]);
```

### Delete a link

```php
Micrologs::deleteLink('aB3xYz12');
```

---

## Analytics

All analytics methods return the full response array from your Micrologs server. Access data via `$result['data']`.

```php
$analytics = Micrologs::analytics();
```

### Common params

All methods accept an optional params array:

| Param  | Default | Description |
|--------|---------|-------------|
| `range` | `"30d"` | `"7d"` / `"30d"` / `"90d"` / `"custom"` |
| `from`  | -       | `"YYYY-MM-DD"` - required when `range="custom"` |
| `to`    | -       | `"YYYY-MM-DD"` - required when `range="custom"` |

---

### Visitors

```php
$result = Micrologs::analytics()->visitors(['range' => '30d']);

// $result['data']:
// [
//     'unique_visitors' => 1842,
//     'total_pageviews' => 5631,
//     'total_sessions'  => 2109,
//     'bounce_rate'     => 43.2,
//     'over_time'       => [...],
// ]
```

### New vs returning visitors

```php
Micrologs::analytics()->returning(['range' => '30d']);
```

### Sessions

```php
Micrologs::analytics()->sessions(['range' => '7d']);
// avg_duration_seconds, avg_duration_engaged, avg_pages_per_session, over_time
```

### Pages, devices, locations, referrers, UTM

```php
Micrologs::analytics()->pages(['range' => '30d']);
Micrologs::analytics()->devices(['range' => '30d']);
Micrologs::analytics()->locations(['range' => '30d']);
Micrologs::analytics()->referrers(['range' => '30d']);
Micrologs::analytics()->utm(['range' => '30d']);
```

### Errors

```php
// All error groups
Micrologs::analytics()->errors(['range' => '30d']);

// Filtered
Micrologs::analytics()->errors([
    'range'       => '30d',
    'status'      => 'open',
    'severity'    => 'critical',
    'environment' => 'production',
]);

// Daily trend
Micrologs::analytics()->errorsTrend(['range' => '30d']);

// Trend for a single group
Micrologs::analytics()->errorsTrend(['range' => '30d', 'group_id' => 12]);

// Full detail for one group
Micrologs::analytics()->errorDetail(['id' => 12]);
```

### Update error status

```php
// Single group
Micrologs::updateErrorStatus(42, 'investigating');
Micrologs::updateErrorStatus(42, 'resolved');

// Bulk
Micrologs::updateErrorStatus([12, 15, 22], 'ignored');
```

Valid statuses: `open` → `investigating` → `resolved` or `ignored`.

### Audit log

```php
Micrologs::analytics()->audits(['range' => '7d']);

// Filtered
Micrologs::analytics()->audits([
    'range'  => '30d',
    'action' => 'user.login',
    'actor'  => 'user@email.com',
]);
```

### Tracked links

```php
Micrologs::analytics()->links(['range' => '30d']);
Micrologs::analytics()->linkDetail(['code' => 'aB3xYz12', 'range' => '30d']);
```

### Custom date range

```php
Micrologs::analytics()->visitors([
    'range' => 'custom',
    'from'  => '2026-01-01',
    'to'    => '2026-01-31',
]);
```

---

## Verify a key

```php
$result = Micrologs::verify('some_key');
```

---

## Error handling

The SDK never throws or crashes your application. If the Micrologs server is unreachable, returns an error, or times out - the method returns `null` and calls Laravel's `report()` function so the failure is logged to your Laravel log, not silently swallowed.

```php
$result = Micrologs::error('Payment failed');

if ($result === null) {
    // SDK call failed - check your Laravel log
    // Your application continues normally regardless
}
```

The 5 second timeout is configurable via `MICROLOGS_TIMEOUT` in `.env`.

---

## Full method reference

| Method | Description |
|--------|-------------|
| `Micrologs::error($message, $options)` | Track an error |
| `Micrologs::audit($action, $actor, $context)` | Track an audit event |
| `Micrologs::createLink($url, $label)` | Create a tracked short link |
| `Micrologs::getLink($code)` | Fetch a single link by code |
| `Micrologs::editLink($code, $options)` | Edit a link's destination, label, or active state |
| `Micrologs::deleteLink($code)` | Delete a link by code |
| `Micrologs::updateErrorStatus($ids, $status)` | Update error group status - single ID or array |
| `Micrologs::verify($key)` | Verify a public or secret key |
| `Micrologs::analytics()->visitors($params)` | Unique visitors, pageviews, sessions, bounce rate |
| `Micrologs::analytics()->returning($params)` | New vs returning visitors |
| `Micrologs::analytics()->sessions($params)` | Session duration, pages per session |
| `Micrologs::analytics()->pages($params)` | Top pages by pageviews |
| `Micrologs::analytics()->devices($params)` | Device, OS, browser breakdown |
| `Micrologs::analytics()->locations($params)` | Country, region, city breakdown |
| `Micrologs::analytics()->referrers($params)` | Traffic sources |
| `Micrologs::analytics()->utm($params)` | UTM campaign data |
| `Micrologs::analytics()->errors($params)` | Error groups with occurrence counts |
| `Micrologs::analytics()->errorsTrend($params)` | Daily error trend, top groups |
| `Micrologs::analytics()->errorDetail($params)` | Single error group - all occurrences and detail |
| `Micrologs::analytics()->audits($params)` | Audit log events |
| `Micrologs::analytics()->links($params)` | Tracked links with click counts |
| `Micrologs::analytics()->linkDetail($params)` | Single link - clicks over time |

---

## Requirements

- Laravel 10, 11, or 12
- PHP 8.1+
- A running [Micrologs](https://github.com/OmDongaonkar03/Micrologs) server (v1.3.0+)

---

## License

MIT - [Om Dongaonkar](https://github.com/OmDongaonkar03)
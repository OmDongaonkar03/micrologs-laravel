<?php

namespace Micrologs\Laravel;

/**
 * Analytics query surface. Access via Micrologs::analytics() or $client->analytics().
 *
 * All methods accept an optional params array.
 *
 * Common params:
 *   range   string  "7d" | "30d" | "90d" | "custom"  (default: "30d")
 *   from    string  "YYYY-MM-DD"  required when range="custom"
 *   to      string  "YYYY-MM-DD"  required when range="custom"
 *
 * @example
 *   Micrologs::analytics()->visitors(['range' => '7d'])
 *   Micrologs::analytics()->visitors(['range' => 'custom', 'from' => '2026-01-01', 'to' => '2026-01-31'])
 */
class Analytics
{
    public function __construct(private MicrologsClient $client) {}

    /** Unique visitors, pageviews, sessions, bounce rate, over time. */
    public function visitors(array $params = []): ?array
    {
        return $this->client->analyticsGet('/api/analytics/visitors.php', $params);
    }

    /** New vs returning visitors, percentage split, over time. */
    public function returning(array $params = []): ?array
    {
        return $this->client->analyticsGet('/api/analytics/visitors-returning.php', $params);
    }

    /** Avg session duration, avg pages per session, over time. */
    public function sessions(array $params = []): ?array
    {
        return $this->client->analyticsGet('/api/analytics/sessions.php', $params);
    }

    /** Top pages by pageviews. */
    public function pages(array $params = []): ?array
    {
        return $this->client->analyticsGet('/api/analytics/pages.php', $params);
    }

    /** Breakdown by device type, OS, browser. */
    public function devices(array $params = []): ?array
    {
        return $this->client->analyticsGet('/api/analytics/devices.php', $params);
    }

    /** Breakdown by country, region, city. */
    public function locations(array $params = []): ?array
    {
        return $this->client->analyticsGet('/api/analytics/locations.php', $params);
    }

    /** Traffic sources. */
    public function referrers(array $params = []): ?array
    {
        return $this->client->analyticsGet('/api/analytics/referrers.php', $params);
    }

    /** UTM campaign data. */
    public function utm(array $params = []): ?array
    {
        return $this->client->analyticsGet('/api/analytics/utm.php', $params);
    }

    /**
     * Error groups with occurrence counts.
     *
     * Optional filters: status, severity, environment
     * @example
     *   Micrologs::analytics()->errors(['range' => '30d', 'status' => 'open', 'severity' => 'critical'])
     */
    public function errors(array $params = []): ?array
    {
        return $this->client->analyticsGet('/api/analytics/errors.php', $params);
    }

    /**
     * Daily error trend, top groups.
     *
     * Pass group_id to scope to a single error group.
     * @example
     *   Micrologs::analytics()->errorsTrend(['range' => '30d', 'group_id' => 12])
     */
    public function errorsTrend(array $params = []): ?array
    {
        return $this->client->analyticsGet('/api/analytics/errors-trend.php', $params);
    }

    /**
     * Single error group with all events.
     *
     * @param  array{id: int} $params  id is required
     */
    public function errorDetail(array $params = []): ?array
    {
        return $this->client->analyticsGet('/api/analytics/error-detail.php', $params);
    }

    /**
     * Audit log events.
     *
     * Optional filters: action, actor
     * @example
     *   Micrologs::analytics()->audits(['range' => '7d', 'action' => 'user.login'])
     */
    public function audits(array $params = []): ?array
    {
        return $this->client->analyticsGet('/api/analytics/audits.php', $params);
    }

    /** All tracked links with click counts. */
    public function links(array $params = []): ?array
    {
        return $this->client->analyticsGet('/api/analytics/links.php', $params);
    }

    /**
     * Single link with clicks over time.
     *
     * @param  array{code: string} $params  code is required
     */
    public function linkDetail(array $params = []): ?array
    {
        return $this->client->analyticsGet('/api/analytics/link-detail.php', $params);
    }
}
?>
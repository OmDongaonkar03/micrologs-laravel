<?php

namespace Micrologs\Laravel;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Response;

class MicrologsClient
{
    private string $host;
    private string $key;
    private HttpFactory $http;
    private int $timeout;

    public function __construct(string $host, string $key, HttpFactory $http, int $timeout = 5)
    {
        $this->host    = rtrim($host, '/');
        $this->key     = $key;
        $this->http    = $http;
        $this->timeout = $timeout;
    }

    // ── Internal helpers ──────────────────────────────────────────────────────

    private function post(string $endpoint, array $payload): ?array
    {
        try {
            $response = $this->http
                ->timeout($this->timeout)
                ->withHeaders(['X-API-Key' => $this->key])
                ->post("{$this->host}{$endpoint}", $payload);

            return $this->parseResponse($response);
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }

    private function get(string $endpoint, array $params = []): ?array
    {
        try {
            $clean = array_filter($params, fn($v) => $v !== null);

            $response = $this->http
                ->timeout($this->timeout)
                ->withHeaders(['X-API-Key' => $this->key])
                ->get("{$this->host}{$endpoint}", $clean);

            return $this->parseResponse($response);
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }

    private function parseResponse(Response $response): ?array
    {
        if ($response->failed()) {
            report(new \RuntimeException(
                "[Micrologs] HTTP {$response->status()}: " . $response->body()
            ));
            return null;
        }

        return $response->json();
    }

    // ── Tracking ──────────────────────────────────────────────────────────────

    /**
     * Track an error from your Laravel application.
     *
     * @param  string       $message
     * @param  array{
     *     type?: string,
     *     severity?: string,
     *     file?: string,
     *     line?: int,
     *     stack?: string,
     *     url?: string,
     *     environment?: string,
     *     context?: array<string, mixed>
     * } $options
     */
    public function error(string $message, array $options = []): ?array
    {
        return $this->post('/api/track/error.php', [
            'message'     => mb_substr($message, 0, 1024),
            'error_type'  => $options['type']        ?? 'LaravelError',
            'severity'    => $options['severity']    ?? 'error',
            'file'        => $options['file']        ?? '',
            'line'        => $options['line']        ?? null,
            'stack'       => $options['stack']       ?? null,
            'url'         => $options['url']         ?? '',
            'environment' => $options['environment'] ?? app()->environment(),
            'context'     => $options['context']     ?? null,
        ]);
    }

    /**
     * Track an audit event.
     *
     * @param  string                    $action   e.g. "user.login", "order.placed"
     * @param  string                    $actor    e.g. "user@email.com" or user ID
     * @param  array<string, mixed>|null $context
     */
    public function audit(string $action, string $actor = '', ?array $context = null): ?array
    {
        if (empty($action)) {
            return null;
        }

        return $this->post('/api/track/audit.php', [
            'action'  => $action,
            'actor'   => $actor,
            'context' => $context,
        ]);
    }

    // ── Link management ───────────────────────────────────────────────────────

    /**
     * Create a tracked short link.
     */
    public function createLink(string $destinationUrl, string $label = ''): ?array
    {
        if (empty($destinationUrl)) {
            return null;
        }

        return $this->post('/api/links/create.php', [
            'destination_url' => $destinationUrl,
            'label'           => $label,
        ]);
    }

    /**
     * Fetch a single tracked link by code.
     */
    public function getLink(string $code): ?array
    {
        if (empty($code)) {
            return null;
        }

        return $this->get('/api/links/detail.php', ['code' => $code]);
    }

    /**
     * Edit a tracked link's destination URL, label, or active state.
     *
     * @param  array{
     *     destination_url?: string,
     *     label?: string,
     *     is_active?: bool
     * } $options
     */
    public function editLink(string $code, array $options = []): ?array
    {
        if (empty($code)) {
            return null;
        }

        $payload = ['code' => $code];

        if (array_key_exists('destination_url', $options)) {
            $payload['destination_url'] = $options['destination_url'];
        }
        if (array_key_exists('label', $options)) {
            $payload['label'] = $options['label'];
        }
        if (array_key_exists('is_active', $options)) {
            $payload['is_active'] = $options['is_active'];
        }

        return $this->post('/api/links/edit.php', $payload);
    }

    /**
     * Delete a tracked link by code.
     */
    public function deleteLink(string $code): ?array
    {
        if (empty($code)) {
            return null;
        }

        return $this->post('/api/links/delete.php', ['code' => $code]);
    }

    /**
     * Update error group status — single ID or array of up to 100 IDs.
     *
     * @param  int|int[] $ids
     * @param  string    $status  "open" | "investigating" | "resolved" | "ignored"
     */
    public function updateErrorStatus(int|array $ids, string $status): ?array
    {
        $valid = ['open', 'investigating', 'resolved', 'ignored'];

        if (!in_array($status, $valid, true)) {
            return null;
        }

        $payload = is_array($ids)
            ? ['ids' => $ids, 'status' => $status]
            : ['id'  => $ids, 'status' => $status];

        return $this->post('/api/track/errors-update-status.php', $payload);
    }

    /**
     * Verify a public or secret key.
     */
    public function verify(string $key): ?array
    {
        return $this->post('/api/projects/verify.php', ['key' => $key]);
    }

    // ── Analytics ─────────────────────────────────────────────────────────────

    /**
     * Access the analytics query surface.
     *
     * @example
     *   Micrologs::analytics()->visitors(['range' => '30d'])
     *   Micrologs::analytics()->errors(['range' => '7d', 'status' => 'open'])
     */
    public function analytics(): Analytics
    {
        return new Analytics($this);
    }

    /**
     * @internal Used by Analytics — not part of the public API.
     */
    public function analyticsGet(string $endpoint, array $params = []): ?array
    {
        return $this->get($endpoint, $params);
    }
}
?>
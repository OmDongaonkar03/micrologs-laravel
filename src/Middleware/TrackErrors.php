<?php

namespace Micrologs\Laravel\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Micrologs\Laravel\MicrologsClient;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

/**
 * Automatically captures unhandled exceptions and sends them to Micrologs.
 *
 * Register globally in bootstrap/app.php (Laravel 11+):
 *
 *   ->withMiddleware(function (Middleware $middleware) {
 *       $middleware->append(\Micrologs\Laravel\Middleware\TrackErrors::class);
 *   })
 *
 * Or in app/Http/Kernel.php (Laravel 10 and below):
 *
 *   protected $middleware = [
 *       \Micrologs\Laravel\Middleware\TrackErrors::class,
 *   ];
 */
class TrackErrors
{
    public function __construct(private MicrologsClient $micrologs) {}

    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        try {
            return $next($request);
        } catch (Throwable $e) {
            $this->track($e, $request);
            throw $e;
        }
    }

    private function track(Throwable $e, Request $request): void
    {
        try {
            $this->micrologs->error($e->getMessage(), [
                'type'        => get_class($e),
                'severity'    => $this->severity($e),
                'file'        => $e->getFile(),
                'line'        => $e->getLine(),
                'stack'       => $e->getTraceAsString(),
                'url'         => $request->fullUrl(),
                'environment' => app()->environment(),
                'context'     => [
                    'method'     => $request->method(),
                    'ip'         => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'user_id'    => optional(auth()->user())->getKey(),
                ],
            ]);
        } catch (Throwable) {
            // Never let tracking failures affect the application
        }
    }

    private function severity(Throwable $e): string
    {
        return match (true) {
            $e instanceof \Error                                  => 'critical',
            $e instanceof \Illuminate\Auth\AuthenticationException,
            $e instanceof \Illuminate\Auth\Access\AuthorizationException => 'warning',
            $e instanceof \Illuminate\Validation\ValidationException     => 'info',
            default                                                       => 'error',
        };
    }
}
?>
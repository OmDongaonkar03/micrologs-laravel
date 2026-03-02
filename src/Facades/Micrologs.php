<?php

namespace Micrologs\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use Micrologs\Laravel\Analytics;
use Micrologs\Laravel\MicrologsClient;

/**
 * @method static array|null error(string $message, array $options = [])
 * @method static array|null audit(string $action, string $actor = '', ?array $context = null)
 * @method static array|null createLink(string $destinationUrl, string $label = '')
 * @method static array|null getLink(string $code)
 * @method static array|null editLink(string $code, array $options = [])
 * @method static array|null deleteLink(string $code)
 * @method static array|null updateErrorStatus(int|array $ids, string $status)
 * @method static array|null verify(string $key)
 * @method static Analytics  analytics()
 *
 * @see MicrologsClient
 */
class Micrologs extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'micrologs';
    }
}
?>
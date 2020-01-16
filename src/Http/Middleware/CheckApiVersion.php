<?php

declare(strict_types=1);

namespace YQueue\ApiSupport\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use InvalidArgumentException;
use YQueue\ApiSupport\Http\ErrorFactory;
use YQueue\ApiSupport\Versioning\ApiVersion;

class CheckApiVersion
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! $request->hasHeader('X-API-VERSION')) {
            return ErrorFactory::unsupportedApiVersion();
        }

        try {
            new ApiVersion($request->header('X-API-VERSION'));
        } catch (InvalidArgumentException $e) {
            return ErrorFactory::unsupportedApiVersion();
        }

        return $next($request);
    }
}

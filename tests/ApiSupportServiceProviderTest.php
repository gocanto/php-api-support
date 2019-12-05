<?php

declare(strict_types=1);

namespace Tests;

use Orchestra\Testbench\TestCase;
use YQueue\ApiSupport\ApiSupportServiceProvider;

class ApiSupportServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [ApiSupportServiceProvider::class];
    }

    public function testConfigurationCreated(): void
    {
        $this->assertTrue(
            $this->app['config']->has('api-support')
        );
    }
}

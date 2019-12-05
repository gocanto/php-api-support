<?php
declare(strict_types=1);

namespace Tests\Database\Pagination;

use Illuminate\Http\Request;
use Orchestra\Testbench\TestCase;
use Symfony\Component\HttpFoundation\Response;
use YQueue\ApiSupport\Database\Pagination\Exceptions\PaginationException;

class PaginationExceptionTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        $app['router']->get('test', function (Request $request) {
            throw new PaginationException('test message');
        });
    }

    public function testExceptionRendersStandardisedResponse(): void
    {
        $response = $this->get('test');

        $response->assertExactJson([
            'error' => 'pagination_error',
            'description' => 'test message',
        ]);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
    }
}

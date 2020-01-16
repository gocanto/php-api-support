<?php
declare(strict_types=1);

namespace Tests\Http\Middleware;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Orchestra\Testbench\TestCase;
use Tests\HasSampleDatesTrait;
use YQueue\ApiSupport\Http\ErrorFactory;
use YQueue\ApiSupport\Http\Middleware\CheckApiVersion;
use YQueue\ApiSupport\Versioning\ApiVersion;

class CheckAPIVersionMiddlewareTest extends TestCase
{
    use HasSampleDatesTrait;

    protected function getEnvironmentSetUp($app): void
    {
        $app['router']->get('test', function (Request $request) {
            return new JsonResponse([
                'version' => (new ApiVersion($request->header('X-API-VERSION')))->toString(),
            ]);
        })->middleware(CheckApiVersion::class);
    }

    public function testHeaderRequired(): void
    {
        $response = $this->get('test');

        $response->assertExactJson(
            json_decode(ErrorFactory::unsupportedApiVersion()->getContent(), true)
        );
    }

    /**
     * @dataProvider invalidDates
     * @param string $invalidDate
     */
    public function testInvalidDateRejected($invalidDate): void
    {
        $response = $this->withHeader('X-API-VERSION', $invalidDate)->get('test');

        $response->assertExactJson(
            json_decode(ErrorFactory::unsupportedApiVersion()->getContent(), true)
        );
    }

    /**
     * @dataProvider validDates
     * @param $validDate
     */
    public function testValidDateAccepted($validDate): void
    {
        $response = $this->withHeader('X-API-VERSION', $validDate)->get('test');

        $response->assertExactJson([
            'version' => $validDate,
        ]);
    }
}

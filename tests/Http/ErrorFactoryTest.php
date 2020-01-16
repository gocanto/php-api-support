<?php
declare(strict_types=1);

namespace Tests\Http;

use Illuminate\Http\JsonResponse;
use PHPUnit\Framework\TestCase;
use YQueue\ApiSupport\Http\ErrorFactory;

class ErrorFactoryTest extends TestCase
{
    public function testArgumentsAreCorrectlySet(): void
    {
        $expected = ['error' => 'test_code', 'description' => 'foo'];

        $response = ErrorFactory::create($expected['error'], $expected['description'], 400);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame($expected, json_decode($response->getContent(), true));
    }

    public function factoryMethodNames(): array
    {
        return [
            ['serverError', JsonResponse::HTTP_INTERNAL_SERVER_ERROR, 'server.error'],
            ['unauthorised', JsonResponse::HTTP_UNAUTHORIZED, 'client.unauthenticated'],
            ['accessTokenExpired', JsonResponse::HTTP_UNAUTHORIZED, 'client.access_token_expired'],
            ['accessTokenRevoked', JsonResponse::HTTP_UNAUTHORIZED, 'client.access_token_revoked'],
            ['insufficientScopes', JsonResponse::HTTP_FORBIDDEN, 'client.insufficient_scopes'],
            ['forbidden', JsonResponse::HTTP_FORBIDDEN, 'client.forbidden'],
            ['badRequest', JsonResponse::HTTP_BAD_REQUEST, 'client.endpoints.invalid-request'],
            ['methodNotAllowed', JsonResponse::HTTP_METHOD_NOT_ALLOWED, 'client.endpoints.invalid-method'],
            ['rateLimitExceeded', JsonResponse::HTTP_TOO_MANY_REQUESTS, 'client.rate_limit_exceeded'],
            ['notFound', JsonResponse::HTTP_NOT_FOUND, 'client.endpoints.not-found'],
            ['conflict', JsonResponse::HTTP_CONFLICT, 'client.endpoints.conflict'],
            ['unsupportedApiVersion', JsonResponse::HTTP_BAD_REQUEST, 'client.unsupported_api_version'],
        ];
    }

    /**
     * @dataProvider factoryMethodNames
     * @param $methodName
     * @param $statusCode
     * @param $error
     */
    public function testTemplates($methodName, $statusCode, $error): void
    {
        $response = call_user_func([ErrorFactory::class, $methodName]);

        $this->assertEquals($statusCode, $response->getStatusCode());
        $this->assertEquals($error, json_decode($response->getContent())->error);
        $this->assertNotEmpty($error, json_decode($response->getContent())->description);
    }

    /**
     * @dataProvider factoryMethodNames
     * @param $methodName
     * @param $statusCode
     * @param $error
     */
    public function testTemplatesWithCustomDescription($methodName, $statusCode, $error): void
    {
        $response = call_user_func([ErrorFactory::class, $methodName], 'foo');

        $this->assertEquals($statusCode, $response->getStatusCode());
        $this->assertEquals($error, json_decode($response->getContent())->error);
        $this->assertEquals('foo', json_decode($response->getContent())->description);
    }
}

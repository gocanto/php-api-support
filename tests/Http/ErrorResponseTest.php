<?php
declare(strict_types=1);

namespace Tests\Http;

use Illuminate\Http\Response;
use PHPUnit\Framework\TestCase;
use YQueue\ApiSupport\Http\ErrorResponse;

class ErrorResponseTest extends TestCase
{
    public function testArgumentsAreCorrectlySet(): void
    {
        $expected = ['error' => 'test_code', 'description' => 'test message'];

        $response = new ErrorResponse($expected['error'], $expected['description'], 400);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame($expected, json_decode($response->getContent(), true));
    }

    public function testContextIsInjected(): void
    {
        $expected = ['error' => 'test_code', 'description' => 'test message'];
        $context = ['some' => 'context'];

        $response = new ErrorResponse($expected['error'], $expected['description'], 400, $context);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame(array_merge($expected, $context), json_decode($response->getContent(), true));
    }

    public function testServerErrorMethod(): void
    {
        $response = ErrorResponse::serverError();

        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertEquals('server.error', json_decode($response->getContent())->error);
    }

    public function testUnauthorizedMethod(): void
    {
        $response = ErrorResponse::unauthorized();

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertEquals('client.unauthenticated', json_decode($response->getContent())->error);
    }

    public function testAccessTokenExpiredMethod(): void
    {
        $response = ErrorResponse::accessTokenExpired();

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertEquals('client.access_token_expired', json_decode($response->getContent())->error);
    }

    public function testAccessTokenRevokedMethod(): void
    {
        $response = ErrorResponse::accessTokenRevoked();

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertEquals('client.access_token_revoked', json_decode($response->getContent())->error);
    }

    public function testInsufficientScopesMethod(): void
    {
        $response = ErrorResponse::insufficientScopes();

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertEquals('client.insufficient_scopes', json_decode($response->getContent())->error);
    }

    public function testForbiddenMethod(): void
    {
        $response = ErrorResponse::forbidden();

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertEquals('client.forbidden', json_decode($response->getContent())->error);
    }

    public function testBadRequestMethod(): void
    {
        $response = ErrorResponse::badRequest();

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals('client.endpoints.invalid-request', json_decode($response->getContent())->error);
    }

    public function testMethodNotAllowedMethod(): void
    {
        $response = ErrorResponse::methodNotAllowed();

        $this->assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        $this->assertEquals('client.endpoints.invalid-method', json_decode($response->getContent())->error);
    }

    public function testRateLimitExceededMethod(): void
    {
        $response = ErrorResponse::rateLimitExceeded();

        $this->assertEquals(Response::HTTP_TOO_MANY_REQUESTS, $response->getStatusCode());
        $this->assertEquals('client.rate_limit_exceeded', json_decode($response->getContent())->error);
    }

    public function testNotFoundMethod(): void
    {
        $response = ErrorResponse::notFound();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertEquals('client.endpoints.not-found', json_decode($response->getContent())->error);
    }

    public function testConflictMethod(): void
    {
        $response = ErrorResponse::conflict();

        $this->assertEquals(Response::HTTP_CONFLICT, $response->getStatusCode());
        $this->assertEquals('client.endpoints.conflict', json_decode($response->getContent())->error);
    }

    public function testUnsupportedApiVersionMethod(): void
    {
        $response = ErrorResponse::unsupportedApiVersion();

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals('client.unsupported_api_version', json_decode($response->getContent())->error);
    }
}

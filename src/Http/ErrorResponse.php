<?php

declare(strict_types=1);

namespace YQueue\ApiSupport\Http;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ErrorResponse extends JsonResponse
{
    public function __construct(string $error, string $description, int $statusCode, array $context = [])
    {
        $data = [
            'error' => $error,
            'description' => $description,
        ];

        if (! empty($context)) {
            $data = array_merge($data, $context); // Permit overriding the default keys via the context
        }

        parent::__construct($data, $statusCode);
    }

    public static function serverError(array $context = []): ErrorResponse
    {
        return new static(
            'server.error',
            'Something went wrong. We\'ve been notified of the error and you can try again shortly.',
            Response::HTTP_INTERNAL_SERVER_ERROR,
            $context
        );
    }

    public static function unauthorized(array $context = []): ErrorResponse
    {
        return new static(
            'client.unauthenticated',
            'You are not authorized to access this resource.',
            Response::HTTP_UNAUTHORIZED,
            $context
        );
    }

    public static function accessTokenExpired(array $context = []): ErrorResponse
    {
        return new static(
            'client.access_token_expired',
            'The provided access token has expired.',
            Response::HTTP_UNAUTHORIZED,
            $context
        );
    }

    public static function accessTokenRevoked(array $context = []): ErrorResponse
    {
        return new static(
            'client.access_token_revoked',
            'The provided access token has been revoked.',
            Response::HTTP_UNAUTHORIZED,
            $context
        );
    }

    public static function insufficientScopes(array $context = []): ErrorResponse
    {
        return new static(
            'client.insufficient_scopes',
            'The provided access token does not have permission to perform the requested operation.',
            Response::HTTP_FORBIDDEN,
            $context
        );
    }

    public static function forbidden(array $context = []): ErrorResponse
    {
        return new static(
            'client.forbidden',
            'The provided access token does not have permission to perform the requested operation.',
            Response::HTTP_FORBIDDEN,
            $context
        );
    }

    public static function badRequest(array $context = []): ErrorResponse
    {
        return new static(
            'client.endpoints.invalid-request',
            'Your request was invalid. Please check the provided information and try again.',
            Response::HTTP_BAD_REQUEST,
            $context
        );
    }

    public static function methodNotAllowed(array $context = []): ErrorResponse
    {
        return new static(
            'client.endpoints.invalid-method',
            'The requested endpoint exists, but does not support the requested HTTP verb.',
            Response::HTTP_METHOD_NOT_ALLOWED,
            $context
        );
    }

    public static function rateLimitExceeded(array $context = []): ErrorResponse
    {
        return new static(
            'client.rate_limit_exceeded',
            'You have exceeded the allocated rate limit. Please try again shortly.',
            Response::HTTP_TOO_MANY_REQUESTS,
            $context
        );
    }

    public static function notFound(array $context = []): ErrorResponse
    {
        return new static(
            'client.endpoints.not-found',
            'The requested resource does not exist.',
            Response::HTTP_NOT_FOUND,
            $context
        );
    }

    public static function conflict(array $context = []): ErrorResponse
    {
        return new static(
            'client.endpoints.conflict',
            'Your request was valid, however a similar operation is in-progress. Please try again shortly.',
            Response::HTTP_CONFLICT,
            $context
        );
    }

    public static function unsupportedApiVersion(array $context = []): ErrorResponse
    {
        return new static(
            'client.unsupported_api_version',
            'The requested API version is not supported.',
            Response::HTTP_BAD_REQUEST,
            $context
        );
    }
}

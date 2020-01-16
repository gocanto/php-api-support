<?php

declare(strict_types=1);

namespace YQueue\ApiSupport\Http;

use Illuminate\Http\JsonResponse;

final class ErrorFactory
{
    /**
     * Create a response with the supplied error and description.
     *
     * @param string $error
     * @param string $description
     * @param int $statusCode
     * @return JsonResponse
     */
    public static function create(string $error, string $description, int $statusCode): JsonResponse
    {
        return new JsonResponse([
            'error' => $error,
            'description' => $description,
        ], $statusCode);
    }

    /**
     * Indicates that a server error has occurred.
     *
     * @param string|null $description
     * @return JsonResponse
     */
    public static function serverError(?string $description = null): JsonResponse
    {
        return static::create(
            'server.error',
            $description ?? 'Something went wrong. We\'ve been notified of the error and you can try again shortly.',
            JsonResponse::HTTP_INTERNAL_SERVER_ERROR
        );
    }

    /**
     * Indicates that authorisation failed.
     *
     * @param string|null $description
     * @return JsonResponse
     */
    public static function unauthorised(?string $description = null): JsonResponse
    {
        return static::create(
            'client.unauthenticated',
            $description ?? 'You are not authorized to access this resource.',
            JsonResponse::HTTP_UNAUTHORIZED
        );
    }

    /**
     * Indicates that the supplied access token has expired.
     *
     * @param string|null $description
     * @return JsonResponse
     */
    public static function accessTokenExpired(?string $description = null): JsonResponse
    {
        return static::create(
            'client.access_token_expired',
            $description ?? 'The provided access token has expired.',
            JsonResponse::HTTP_UNAUTHORIZED
        );
    }

    /**
     * Indicates that the supplied access token has been revoked.
     *
     * @param string|null $description
     * @return JsonResponse
     */
    public static function accessTokenRevoked(?string $description = null): JsonResponse
    {
        return static::create(
            'client.access_token_revoked',
            $description ?? 'The provided access token has been revoked.',
            JsonResponse::HTTP_UNAUTHORIZED
        );
    }

    /**
     * Indicates that the scopes associated with the supplied access token do not permit the requested operation.
     *
     * @param string|null $description
     * @return JsonResponse
     */
    public static function insufficientScopes(?string $description = null): JsonResponse
    {
        return static::create(
            'client.insufficient_scopes',
            $description ?? 'The provided access token does not have permission to perform the requested operation.',
            JsonResponse::HTTP_FORBIDDEN
        );
    }

    /**
     * Indicates that the client is forbidden from performing the requested operation.
     *
     * @param string|null $description
     * @return JsonResponse
     */
    public static function forbidden(?string $description = null): JsonResponse
    {
        return static::create(
            'client.forbidden',
            $description ?? 'The provided access token does not have permission to perform the requested operation.',
            JsonResponse::HTTP_FORBIDDEN
        );
    }

    /**
     * Indicates that the request was malformed.
     *
     * @param string|null $description
     * @return JsonResponse
     */
    public static function badRequest(?string $description = null): JsonResponse
    {
        return static::create(
            'client.endpoints.invalid-request',
            $description ?? 'Your request was invalid. Please check the provided information and try again.',
            JsonResponse::HTTP_BAD_REQUEST
        );
    }

    /**
     * Indicates that an incorrect HTTP verb was used.
     *
     * @param string|null $description
     * @return JsonResponse
     */
    public static function methodNotAllowed(?string $description = null): JsonResponse
    {
        return static::create(
            'client.endpoints.invalid-method',
            $description ?? 'The requested endpoint exists, but does not support the requested HTTP verb.',
            JsonResponse::HTTP_METHOD_NOT_ALLOWED
        );
    }

    /**
     * Indicates that the client has hit their permitted request rate.
     *
     * @param string|null $description
     * @return JsonResponse
     */
    public static function rateLimitExceeded(?string $description = null): JsonResponse
    {
        return static::create(
            'client.rate_limit_exceeded',
            $description ?? 'You have exceeded the allocated rate limit. Please try again shortly.',
            JsonResponse::HTTP_TOO_MANY_REQUESTS
        );
    }

    /**
     * Indicates the requested resource was not found.
     *
     * @param string|null $description
     * @return JsonResponse
     */
    public static function notFound(?string $description = null): JsonResponse
    {
        return static::create(
            'client.endpoints.not-found',
            $description ?? 'The requested resource does not exist.',
            JsonResponse::HTTP_NOT_FOUND
        );
    }

    /**
     * Indicates that ths requested operation conflicts with another.
     *
     * @param string|null $description
     * @return JsonResponse
     */
    public static function conflict(?string $description = null): JsonResponse
    {
        return static::create(
            'client.endpoints.conflict',
            $description ?? 'Your request was valid, however a similar operation is in-progress. Please try again shortly.',
            JsonResponse::HTTP_CONFLICT
        );
    }

    /**
     * Indicates that the client requested an unsupported API version.
     *
     * @param string|null $description
     * @return JsonResponse
     */
    public static function unsupportedApiVersion(?string $description = null): JsonResponse
    {
        return static::create(
            'client.unsupported_api_version',
            $description ?? 'The requested API version is not supported.',
            JsonResponse::HTTP_BAD_REQUEST
        );
    }
}

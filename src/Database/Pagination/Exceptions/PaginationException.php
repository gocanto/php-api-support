<?php

namespace YQueue\ApiSupport\Database\Pagination\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use YQueue\ApiSupport\Exceptions\ExceptionInterface;
use YQueue\ApiSupport\Http\ErrorFactory;

class PaginationException extends Exception implements ExceptionInterface
{
    /**
     * @inheritDoc
     */
    public function render(Request $response): JsonResponse
    {
        return ErrorFactory::create(
            'pagination_error',
            $this->message,
            JsonResponse::HTTP_BAD_REQUEST
        );
    }
}

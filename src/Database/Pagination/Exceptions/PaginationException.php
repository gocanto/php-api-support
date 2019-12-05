<?php

namespace YQueue\ApiSupport\Database\Pagination\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use YQueue\ApiSupport\Exceptions\ExceptionInterface;
use YQueue\ApiSupport\Http\ErrorResponse;

class PaginationException extends Exception implements ExceptionInterface
{
    /**
     * @inheritDoc
     */
    public function render(Request $response): ErrorResponse
    {
        return new ErrorResponse(
            'pagination_error',
            $this->message,
            Response::HTTP_BAD_REQUEST
        );
    }
}

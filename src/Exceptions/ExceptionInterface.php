<?php

declare(strict_types=1);

namespace YQueue\ApiSupport\Exceptions;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

interface ExceptionInterface
{
    /**
     * Generate a standardised error response for this exception.
     *
     * @param Request $request
     * @return Response
     */
    public function render(Request $request);
}

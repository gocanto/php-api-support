<?php

declare(strict_types=1);

namespace YQueue\ApiSupport\Database\Pagination;

use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use YQueue\ApiSupport\Database\Pagination\Exceptions\PaginationException;

class PaginatedRequest
{
    const DEFAULT_LIMIT = 10;
    const MIN_LIMIT = 1;
    const MAX_LIMIT = 20;

    /**
     * The total number of records that should be retrieved.
     *
     * @var int
     */
    private $limit;

    /**
     * The cursor for the start of the request.
     *
     * @var string|null
     */
    private $cursor;

    /**
     * @param int $limit
     * @param string|null $cursor
     */
    public function __construct(int $limit, ?string $cursor = null)
    {
        $this->limit = $limit;
        $this->cursor = $cursor;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return string|null
     */
    public function getCursor(): ?string
    {
        return $this->cursor;
    }

    /**
     * Create a cursor from the supplied request instance.
     *
     * @param Request $request
     * @return PaginatedRequest
     * @throws PaginationException
     */
    public static function fromIlluminate(Request $request): PaginatedRequest
    {
        // Set a suitable default limit
        $limit = static::DEFAULT_LIMIT;

        // If the client has requested a specific limit, validate that it's within the permitted range
        if ($request->filled('cursor_limit')) {
            if (filter_var($request->input('cursor_limit'), FILTER_VALIDATE_INT) === false) {
                throw new PaginationException('Pagination only supports a limit between 1 and 20.');
            }

            if ($request->input('cursor_limit') < static::MIN_LIMIT
                || $request->input('cursor_limit') > static::MAX_LIMIT
            ) {
                throw new PaginationException(
                    'Pagination only supports a limit between ' . static::MIN_LIMIT . ' and ' . static::MAX_LIMIT . '.'
                );
            }

            $limit = (int)$request->input('cursor_limit');
        }

        // If we have a cursor, make sure it's a UUID
        if ($request->filled('cursor') && !Uuid::isValid($request->input('cursor'))) {
            throw new PaginationException('An invalid pagination cursor was provided.');
        }

        return new static($limit, $request->input('cursor'));
    }
}

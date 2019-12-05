<?php

declare(strict_types=1);

namespace YQueue\ApiSupport\Database\Pagination;

use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use YQueue\ApiSupport\Http\Transformers\AbstractTransformer;
use YQueue\ApiSupport\Versioning\ApiVersion;

class CursorPaginator
{
    /**
     * @var Collection
     */
    private $results;

    /**
     * @var string|null
     */
    private $nextCursor;

    /**
     * @param Collection $results
     * @param string|null $nextCursor
     */
    private function __construct(Collection $results, ?string $nextCursor = null)
    {
        $this->results = $results;
        $this->nextCursor = $nextCursor;
    }

    /**
     * @return Collection
     */
    public function getResults(): Collection
    {
        return $this->results;
    }

    /**
     * @return string|null
     */
    public function getNextCursor(): ?string
    {
        return $this->nextCursor;
    }

    /**
     * Transform the results using the supplied transformer.
     *
     * @param AbstractTransformer $transformer
     * @param ApiVersion $targetApiVersion
     * @return JsonResponse
     */
    public function toJsonResponse($transformer, ApiVersion $targetApiVersion): JsonResponse
    {
        return new JsonResponse([
            'data' => $transformer->transformCollection($this->getResults(), $targetApiVersion),
            'meta' => [
                'next_cursor' => $this->getNextCursor(),
            ],
        ]);
    }

    /**
     * Transform the results using the supplied transformer, adhering to our legacy pagination structure.
     *
     * @param $transformer
     * @param ApiVersion $targetApiVersion
     * @return JsonResponse
     */
    public function toLegacyJsonResponse($transformer, ApiVersion $targetApiVersion): JsonResponse
    {
        return new JsonResponse([
            'data' => $transformer->transformCollection($this->getResults(), $targetApiVersion),
            'meta' => [
                'cursor' => [
                    'previous' => null, // This field was never used by any of our clients, so we don't need to support it
                    'current' => $this->getResults()->first()->uuid ?? null,
                    'next' => $this->getNextCursor(),
                ],
            ],
        ]);
    }

    /**
     * @param PaginatedRequest $request
     * @param Builder|EloquentBuilder $query
     * @return CursorPaginator
     * @throws Exception
     */
    public static function fromPaginatedRequest(PaginatedRequest $request, $query): CursorPaginator
    {
        // Extract the base query
        $baseQuery = is_a($query, EloquentBuilder::class) ? $query->toBase() : $query;

        // We require an order by clause to be set
        if (empty($baseQuery->orders)) {
            throw new Exception(
                'An order-by clause must be set to use pagination.'
            );
        }

        // Ensure we start from the supplied cursor (if any)
        if ($request->getCursor() !== null) {
            // Extract the table alias, if any
            $tableAlias = last(explode(' ', $baseQuery->from));

            $resolvedCursor = (clone $baseQuery)
                ->where($tableAlias . '.uuid', '=', $request->getCursor())
                ->first();

            // If we didn't find the cursor we can't return any further results
            if ($resolvedCursor === null) {
                return new static(new Collection);
            }

            foreach ($baseQuery->orders as $order) {
                $query->having(
                        $order['column'],
                        $order['direction'] == 'asc' ? '>=' : '<=',
                        $resolvedCursor->{$order['column']}
                    );
            }
        }

        // To determine if there are more results, we retrieve an extra record than requested (and remove it later)
        $query->limit($request->getLimit() + 1);

        // Retrieve our results
        $results = $query->get();

        // If we found an additional record, we have a cursor to return
        if ($results->count() > $request->getLimit()) {
            $nextCursor = $results->pop();
        }

        return new static($results, $nextCursor->uuid ?? null);
    }
}

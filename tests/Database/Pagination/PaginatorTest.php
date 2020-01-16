<?php

namespace Tests\Database\Pagination;

use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase;
use Ramsey\Uuid\Uuid;
use YQueue\ApiSupport\Database\Pagination\CursorPaginator;
use YQueue\ApiSupport\Database\Pagination\PaginatedRequest;
use YQueue\ApiSupport\Http\Transformers\AbstractTransformer;
use YQueue\ApiSupport\Versioning\ApiVersion;

class PaginatorTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

            $this->getConnection()->getSchemaBuilder()->create('tests', function (Blueprint $table) {
                $table->increments('id');
                $table->uuid('uuid')->unique();
                $table->timestamps();
            });

            foreach (range(1, 40) as $index) {
                $this->getConnection()->table('tests')->insert([
                    'uuid' => (string)Uuid::uuid4(),
                    'created_at' => CarbonImmutable::now('UTC')->toDateTimeString(),
                    'updated_at' => CarbonImmutable::now('UTC')->toDateTimeString(),
                ]);
            }
    }

    public function tearDown(): void
    {
        $this->getConnection()->getSchemaBuilder()->drop('tests');

        parent::tearDown();
    }

    public function testOrderByQueryRequired(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('An order-by clause must be set to use pagination.');

        CursorPaginator::fromPaginatedRequest(
            new PaginatedRequest(10, Uuid::NIL),
            $this->getConnection()->query()->from('tests')
        );
    }

    public function testHandlesNoResults(): void
    {
        $paginator = CursorPaginator::fromPaginatedRequest(
            new PaginatedRequest(10),
            $this->getConnection()->query()->from('tests')->where('id', PHP_INT_MAX)->orderByDesc('id')
        );

        $this->assertEmpty($paginator->getResults());
        $this->assertNull($paginator->getNextCursor());
    }

    public function testHandlesNonExistentCursor(): void
    {
        $paginator = CursorPaginator::fromPaginatedRequest(
            new PaginatedRequest(10, Uuid::NIL),
            $this->getConnection()->query()->from('tests')->orderByDesc('id')
        );

        $this->assertEmpty($paginator->getResults());
        $this->assertNull($paginator->getNextCursor());
    }

    public function testOrderByRespected(): void
    {
        $paginator = CursorPaginator::fromPaginatedRequest(
            new PaginatedRequest(10),
            $this->getConnection()->query()->from('tests')->orderBy('id', 'asc')
        );

        $this->assertSame(
            range(1, 10),
            $paginator->getResults()->pluck('id')->map(function ($value) { return intval($value); })->toArray()
        );

        $paginator = CursorPaginator::fromPaginatedRequest(
            new PaginatedRequest(10),
            $this->getConnection()->query()->from('tests')->orderBy('id', 'desc')
        );

        $this->assertSame(
            range(40, 31),
            $paginator->getResults()->pluck('id')->map(function ($value) { return intval($value); })->toArray()
        );
    }

    public function testRespectsLimit(): void
    {
        $count = rand(5, 10);

        $this->assertCount(
            $count,
            CursorPaginator::fromPaginatedRequest(
                new PaginatedRequest($count),
                $this->getConnection()->query()->from('tests')->orderBy('id', 'desc')
            )->getResults()
        );
    }

    public function testStartsFromCursor(): void
    {
        $cursor = $this->getConnection()->query()->from('tests')->where('id', 20)->first();

        $paginator = CursorPaginator::fromPaginatedRequest(
            new PaginatedRequest(10, $cursor->uuid),
            $this->getConnection()->query()->from('tests')->orderByDesc('id')
        );

        $this->assertSame($cursor->uuid, $paginator->getResults()->first()->uuid);
    }

    public function testReturnsCorrectNextCursor(): void
    {
        $nextCursor = $this->getConnection()->query()->from('tests')->where('id', 11)->first();

        $paginator = CursorPaginator::fromPaginatedRequest(
            new PaginatedRequest(10),
            $this->getConnection()->query()->from('tests')->orderBy('id', 'asc')
        );

        $this->assertSame($nextCursor->uuid, $paginator->getNextCursor());
    }

    public function testSupportsTableAndOrderByAliases(): void
    {
        $paginator = CursorPaginator::fromPaginatedRequest(
            new PaginatedRequest(10),
            $this->getConnection()->query()->from('tests as table_alias')
                ->selectRaw('*, id as id_alias')
                ->orderBy('id_alias', 'asc')
        );

        $this->assertCount(10, $paginator->getResults());
        $this->assertSame(1, intval($paginator->getResults()->first()->id));
    }

    public function testCanIterateThroughEntireSet(): void
    {
        $retrieved = [];
        $nextCursor = null;

        do {
            $paginator = CursorPaginator::fromPaginatedRequest(
                new PaginatedRequest(10, $nextCursor),
                $this->getConnection()->query()->from('tests')->orderBy('id', 'asc')
            );

            $retrieved = array_merge(
                $retrieved,
                $paginator->getResults()->map(function ($value) {
                    return intval($value->id);
                })->toArray()
            );

            $nextCursor = $paginator->getNextCursor();
        } while ($nextCursor !== null);

        $this->assertSame(range(1, 40), $retrieved);
    }

    public function testToJsonResponse(): void
    {
        $rawResults = $this->getConnection()->query()->from('tests')
            ->orderBy('id', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($value) {
                return (array) $value;
            })
            ->toArray();

        $paginator = CursorPaginator::fromPaginatedRequest(
            new PaginatedRequest(10),
            $this->getConnection()->query()->from('tests')->orderBy('id', 'asc')
        );

        $transformer = new class extends AbstractTransformer {
            /**
             * @inheritDoc
             */
            protected function getBaseData($model, ApiVersion $targetApiVersion): array
            {
                return (array)$model;
            }
        };

        $expected = [
            'data' => $rawResults,
            'meta' => [
                'next_cursor' => $paginator->getNextCursor(),
            ],
        ];

        $this->assertEquals($expected, $paginator->toJsonResponse($transformer, new ApiVersion('01-01-2020'))->getData(true));
    }

    public function testToLegacyJsonResponse(): void
    {
        $rawResults = $this->getConnection()->query()->from('tests')
            ->orderBy('id', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($value) {
                return (array) $value;
            })
            ->toArray();

        $paginator = CursorPaginator::fromPaginatedRequest(
            new PaginatedRequest(10),
            $this->getConnection()->query()->from('tests')->orderBy('id', 'asc')
        );

        $transformer = new class extends AbstractTransformer {
            /**
             * @inheritDoc
             */
            protected function getBaseData($model, ApiVersion $targetApiVersion): array
            {
                return (array)$model;
            }
        };

        $expected = [
            'data' => $rawResults,
            'meta' => [
                'cursor' => [
                    'previous' => null,
                    'current' => $paginator->getResults()->first()->uuid ?? null,
                    'next' => $paginator->getNextCursor(),
                ],
            ],
        ];

        $this->assertEquals($expected, $paginator->toLegacyJsonResponse($transformer, new ApiVersion('01-01-2020'))->getData(true));
    }
}

<?php
declare(strict_types=1);

namespace Tests\Database\Pagination;

use Illuminate\Http\Request;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use YQueue\ApiSupport\Database\Pagination\Exceptions\PaginationException;
use YQueue\ApiSupport\Database\Pagination\PaginatedRequest;

class PaginatedRequestTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testArgumentsAreSet(): void
    {
        $request = new PaginatedRequest(13, Uuid::NIL);
        $this->assertSame(13, $request->getLimit());
        $this->assertSame(Uuid::NIL, $request->getCursor());
    }

    public function testCursorCanBeNull(): void
    {
        $this->assertNull((new PaginatedRequest(10, null))->getCursor());
        $this->assertNull((new PaginatedRequest(10))->getCursor());
    }

    public function testFormRequestMethodHandlesDefaults(): void
    {
        $request = PaginatedRequest::fromIlluminate(new Request);

        $this->assertSame(PaginatedRequest::DEFAULT_LIMIT, $request->getLimit());
        $this->assertNull($request->getCursor());
    }

    public function testFromRequestMethodHandlesStringLimit(): void
    {
        $request = PaginatedRequest::fromIlluminate(new Request([
            'cursor_limit' => '10',
        ]));

        $this->assertSame(10, $request->getLimit());
    }

    public function testFromRequestMethodHandlesIntegerLimit(): void
    {
        $request = PaginatedRequest::fromIlluminate(new Request([
            'cursor_limit' => 10,
        ]));

        $this->assertSame(10, $request->getLimit());
    }

    public function testFromRequestMethodEnforcesMinLimit(): void
    {
        $this->expectException(PaginationException::class);

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('filled')->with('cursor_limit')->andReturn(true);
        $request->shouldReceive('input')->with('cursor_limit')->andReturn(
            PaginatedRequest::MIN_LIMIT - 1
        );

        PaginatedRequest::fromIlluminate($request);
    }

    public function testFromRequestMethodEnforcesMaxLimit(): void
    {
        $this->expectException(PaginationException::class);

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('filled')->once()->with('cursor_limit')->andReturn(true);
        $request->shouldReceive('input')->with('cursor_limit')->andReturn(
            PaginatedRequest::MAX_LIMIT + 1
        );

        PaginatedRequest::fromIlluminate($request);
    }

    public function testFromRequestMethodRejectsNonNumericLimit(): void
    {
        $this->expectException(PaginationException::class);

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('filled')->once()->with('cursor_limit')->andReturn(true);
        $request->shouldReceive('input')->with('cursor_limit')->andReturn('foo');

        PaginatedRequest::fromIlluminate($request);
    }

    public function testFromRequestMethodValidatesCursor(): void
    {
        $this->expectException(PaginationException::class);

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('filled')->with('cursor_limit')->andReturn(false);
        $request->shouldReceive('filled')->once()->with('cursor')->andReturn(true);
        $request->shouldReceive('input')->once()->with('cursor')->andReturn('foo');

        PaginatedRequest::fromIlluminate($request);
    }

    public function testFromRequestMethodAcceptsValidInput(): void
    {
        $limit = rand(PaginatedRequest::MIN_LIMIT, PaginatedRequest::MAX_LIMIT);
        $uuid = (string)Uuid::uuid4();

        $request = PaginatedRequest::fromIlluminate(new Request([
            'cursor_limit' => $limit,
            'cursor' => $uuid,
        ]));

        $this->assertEquals($limit, $request->getLimit());
        $this->assertEquals($uuid, $request->getCursor());
    }
}

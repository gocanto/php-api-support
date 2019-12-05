<?php

declare(strict_types=1);

namespace Tests\Utility;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Exception;
use PHPUnit\Framework\TestCase;
use YQueue\ApiSupport\Utility\DateUtils;

class DateUtilsTest extends TestCase
{
    /**
     * @test
     */
    public function itProperlyParseTheGivenDateString()
    {
        $date = DateUtils::parse('2019-12-04');

        $this->assertInstanceOf(CarbonImmutable::class, $date);
        $this->assertSame('2019-12-04', $date->toDateString());
    }

    /**
     * @test
     */
    public function itThrowsAnExceptionOnInvalidDatesString()
    {
        $this->expectException(Exception::class);

        DateUtils::parse('foo');
    }

    /**
     * @test
     */
    public function itReturnsNullWhenGiven()
    {
        $this->assertNull(DateUtils::parse(null));
    }

    /**
     * @test
     */
    public function itReturnsCarbonInterfaceWhenGiven()
    {
        $now = Carbon::now();
        $date = DateUtils::parse($now);

        $this->assertInstanceOf(Carbon::class, $date);
        $this->assertEquals($date, $now);

        $now = CarbonImmutable::now();
        $date = DateUtils::parse($now);

        $this->assertInstanceOf(CarbonImmutable::class, $date);
        $this->assertEquals($date, $now);
    }
}
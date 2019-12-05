<?php

declare(strict_types=1);

namespace Tests\Versioning;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tests\HasSampleDatesTrait;
use YQueue\ApiSupport\Versioning\ApiVersion;

class ApiVersionTest extends TestCase
{
    use HasSampleDatesTrait;

    /**
     * @dataProvider invalidDates
     * @param string $invalidDate
     */
    public function testValidDateRequired($invalidDate): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ApiVersion($invalidDate);
    }

    /**
     * @dataProvider validDates
     * @param $validDate
     */
    public function testValidDateAcceptedAndSet($validDate): void
    {
        $instance = new ApiVersion($validDate);

        $this->assertSame($validDate, $instance->toString());
    }

    public function testLt(): void
    {
        $instance = new ApiVersion('02-01-2020');

        $this->assertTrue($instance->earlierThan(new ApiVersion('03-01-2020')));
        $this->assertFalse($instance->earlierThan(new ApiVersion('01-01-2020')));
    }

    public function testLte(): void
    {
        $instance = new ApiVersion('02-01-2020');

        $this->assertTrue($instance->earlierThanOrEqualTo(new ApiVersion('03-01-2020')));
        $this->assertTrue($instance->earlierThanOrEqualTo(new ApiVersion('02-01-2020')));
        $this->assertFalse($instance->earlierThanOrEqualTo(new ApiVersion('01-01-2020')));
    }

    public function testGt(): void
    {
        $instance = new ApiVersion('02-01-2020');

        $this->assertTrue($instance->newerThan(new ApiVersion('01-01-2020')));
        $this->assertFalse($instance->newerThan(new ApiVersion('03-01-2020')));
    }

    public function testGte(): void
    {
        $instance = new ApiVersion('02-01-2020');

        $this->assertTrue($instance->newerThanOrEqualTo(new ApiVersion('01-01-2020')));
        $this->assertTrue($instance->newerThanOrEqualTo(new ApiVersion('02-01-2020')));
        $this->assertFalse($instance->newerThanOrEqualTo(new ApiVersion('03-01-2020')));
    }
}

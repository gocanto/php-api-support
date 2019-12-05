<?php

declare(strict_types=1);

namespace YQueue\ApiSupport\Versioning;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

final class ApiVersion
{
    /**
     * @var CarbonImmutable
     */
    private $date;

    /**
     * @param string $version
     * @throws InvalidArgumentException
     */
    public function __construct(string $version)
    {
        $this->date = $this->dateToCarbon($version);
    }

    /**
     * Determine if the supplied version is older as this api version.
     *
     * @param ApiVersion $version
     * @return bool
     */
    public function earlierThan(ApiVersion $version): bool
    {
        return $this->date->lt($version->date());
    }

    /**
     * Determine if the supplied version is older (or the same) as this api version.
     *
     * @param ApiVersion $version
     * @return bool
     */
    public function earlierThanOrEqualTo(ApiVersion $version): bool
    {
        return $this->date->lte($version->date());
    }

    /**
     * Determine if the supplied version is newer as this api version.
     *
     * @param ApiVersion $version
     * @return bool
     */
    public function newerThan(ApiVersion $version): bool
    {
        return $this->date->gt($version->date());
    }

    /**
     * Determine if the supplied version is newer (or the same) as this api version.
     *
     * @param ApiVersion $version
     * @return bool
     */
    public function newerThanOrEqualTo(ApiVersion $version): bool
    {
        return $this->date->gte($version->date());
    }

    /**
     * @return CarbonImmutable
     */
    public function date(): CarbonImmutable
    {
        return $this->date;
    }

    /**
     * A string representation of this api version.
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->date->format('d-m-Y');
    }

    /**
     * Convert a d/m/y string to a carbon instance.
     *
     * @param string $date
     * @return CarbonImmutable
     * @throws InvalidArgumentException
     */
    private function dateToCarbon(string $date): CarbonImmutable
    {
        /** @var CarbonImmutable $carbon */
        $carbon = CarbonImmutable::createFromFormat('!d-m-Y', $date, 'UTC');

        // Ensure that PHP din't try to "guess" the date
        if ($carbon->format('d-m-Y') !== $date) {
            throw new InvalidArgumentException('The supplied API version is invalid.');
        }

        return $carbon;
    }
}

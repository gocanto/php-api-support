<?php

declare(strict_types=1);

namespace YQueue\ApiSupport\Utility;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class DateUtils
{
    /**
     * @param $date
     * @return CarbonInterface|null
     */
    public static function parse($date): ?CarbonInterface
    {
        if (is_string($date)) {
            return CarbonImmutable::parse($date);
        }

        if ($date instanceof CarbonInterface) {
            return $date;
        }

        return null;
    }
}

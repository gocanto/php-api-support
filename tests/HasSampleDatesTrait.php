<?php
declare(strict_types=1);

namespace Tests;

trait HasSampleDatesTrait
{
    /**
     * A list of dates that should not be accepted.
     *
     * @return array
     */
    public function invalidDates(): array
    {
        return [
            ['40-10-2019'],
            ['10-13-2019'],
            ['00-10-0000'],
            ['29-02-2019'],
            ['foo'],
            ['aa-bb-cccc'],
        ];
    }

    /**
     * A list of dates that should be accepted.
     *
     * @return array
     */
    public function validDates(): array
    {
        return [
            ['01-01-2019'],
            ['29-02-2020'],
        ];
    }
}

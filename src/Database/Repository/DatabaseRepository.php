<?php

declare(strict_types=1);

namespace YQueue\ApiSupport\Database\Repository;

use Illuminate\Database\Query\Builder;

abstract class DatabaseRepository
{
    /**
     * Retrieve a basic query builder for the entity.
     *
     * @return Builder
     */
    abstract protected function getBuilder();
}

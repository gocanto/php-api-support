<?php

declare(strict_types=1);

namespace YQueue\ApiSupport\Http\Transformers;

use Illuminate\Database\Eloquent\Model;
use stdClass;
use YQueue\ApiSupport\Versioning\ApiVersion;

/**
 * Encapsulates a single transformation step that must be performed.
 *
 * Consider the following scenario: There are three API versions; 03-01-2020, 02-01-2020 & 01-01-2020.
 *
 * If this step targets 02-01-2020, and the requested API version is 03-01-2020 this step should not be
 * executed. If the requested API version was 03-01-2020, it should be executed.
 */
interface TransformationStep
{
    /**
     * The transformation that this step will perform.
     *
     * @param array $data The data that has already been transformed.
     * @param Model|stdClass $model The source model that is being transformed.
     * @return array
     */
    public function transform(array $data, $model): array;

    /**
     * The API version that this step targets.
     *
     * @return ApiVersion
     */
    public function apiVersion(): ApiVersion;
}

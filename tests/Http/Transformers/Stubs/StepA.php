<?php

namespace Tests\Http\Transformers\Stubs;

use Tests\Http\Transformers\StepTracker;
use YQueue\ApiSupport\Http\Transformers\TransformationStep;
use YQueue\ApiSupport\Versioning\ApiVersion;

class StepA implements TransformationStep
{
    /**
     * @inheritDoc
     */
    public function transform(array $data, $model, ApiVersion $apiVersion): array
    {
        StepTracker::getInstance()->track(static::class);

        return array_merge($data, [
            'step_a' => true,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function apiVersion(): ApiVersion
    {
        return new ApiVersion('02-01-2020');
    }
}

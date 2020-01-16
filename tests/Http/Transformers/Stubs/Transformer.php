<?php

namespace Tests\Http\Transformers\Stubs;

use Illuminate\Database\Eloquent\Model;
use YQueue\ApiSupport\Http\Transformers\AbstractTransformer;
use YQueue\ApiSupport\Versioning\ApiVersion;

class Transformer extends AbstractTransformer
{
    /**
     * @var array
     */
    private $expectedSteps;

    /**
     * @param array $steps
     */
    public function __construct(array $steps = [])
    {
        $this->expectedSteps = $steps;
    }

    /**
     * @inheritDoc
     */
    protected function getBaseData($model, ApiVersion $targetApiVersion): array
    {
        if ($model instanceof Model) {
            return $model->toArray();
        } else {
            return (array)$model;
        }
    }

    /**
     * @inheritDoc
     */
    protected function steps(): array
    {
        return $this->expectedSteps;
    }
}

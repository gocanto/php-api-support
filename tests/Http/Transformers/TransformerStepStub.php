<?php

namespace Tests\Http\Transformers;

use YQueue\ApiSupport\Http\Transformers\TransformationStep;
use YQueue\ApiSupport\Versioning\ApiVersion;

class TransformerStepStub implements TransformationStep
{
    private $apiVersion;
    private $mergeData;

    public function __construct(ApiVersion $apiVersion, array $mergeData = [])
    {
        $this->apiVersion = $apiVersion;
        $this->mergeData = $mergeData;
    }

    /**
     * @inheritDoc
     */
    public function transform(array $data, $model): array
    {
        return array_merge($data, $this->mergeData);
    }

    /**
     * @inheritDoc
     */
    public function apiVersion(): ApiVersion
    {
        return $this->apiVersion;
    }
}

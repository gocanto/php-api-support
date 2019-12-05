<?php

declare(strict_types=1);

namespace YQueue\ApiSupport\Http\Transformers;

use Illuminate\Database\Eloquent\Model;
use YQueue\ApiSupport\Versioning\ApiVersion;

abstract class AbstractTransformer
{
    /**
     * @param Model|object|mixed $model
     * @return array
     */
    abstract protected function getBaseData($model): array;

    /**
     * @param Model|object|mixed $model
     * @param ApiVersion $apiVersion
     * @return array|null
     */
    public function transformModel($model, ApiVersion $apiVersion): ?array
    {
        if ($model === null) {
            return null;
        }

        return $this->transform(
            $model,
            $apiVersion
        );
    }

    /**
     * @param iterable $models
     * @param ApiVersion $apiVersion
     * @return array
     */
    public function transformCollection(iterable $models, ApiVersion $apiVersion): array
    {
        if (empty($models)) {
            return [];
        }

        $data = [];

        foreach ($models as $model) {
            $data[] = $this->transform($model, $apiVersion);
        }

        return $data;
    }

    /**
     * The transformation steps that should be processed.
     *
     * @return TransformationStep[]
     */
    protected function steps(): array
    {
        return [];
    }

    /**
     * @param Model|object|mixed $model
     * @param ApiVersion $targetApiVersion
     * @return array
     */
    private function transform($model, ApiVersion $targetApiVersion) : array
    {
        $data = $this->getBaseData($model);

        foreach ($this->steps() as $step) {
            if ($this->shouldRunStep($step, $targetApiVersion)) {
                $data = $step->transform($data, $model);
            }
        }

        return $data;
    }

    /**
     * Determine if the supplied transformation step should be executed.
     *
     * @param TransformationStep $step
     * @param ApiVersion $targetApiVersion
     * @return bool
     */
    private function shouldRunStep(TransformationStep $step, ApiVersion $targetApiVersion): bool
    {
        return $targetApiVersion->earlierThanOrEqualTo(
            $step->apiVersion()
        );
    }
}

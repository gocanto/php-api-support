<?php

declare(strict_types=1);

namespace YQueue\ApiSupport\Http\Transformers;

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use YQueue\ApiSupport\Versioning\ApiVersion;

abstract class AbstractTransformer
{
    /**
     * @param Model|object|mixed $model
     * @param ApiVersion $targetApiVersion
     * @return array
     */
    abstract protected function getBaseData($model, ApiVersion $targetApiVersion): array;

    /**
     * @param Model|object|mixed $model
     * @param ApiVersion $targetApiVersion
     * @return array|null
     */
    public function transformModel($model, ApiVersion $targetApiVersion): ?array
    {
        if ($model === null) {
            return null;
        }

        return $this->transform(
            $model,
            $targetApiVersion
        );
    }

    /**
     * @param iterable $models
     * @param ApiVersion $targetApiVersion
     * @return array
     */
    public function transformCollection(iterable $models, ApiVersion $targetApiVersion): array
    {
        if (empty($models)) {
            return [];
        }

        $data = [];

        foreach ($models as $model) {
            $data[] = $this->transform($model, $targetApiVersion);
        }

        return $data;
    }

    /**
     * The transformation steps that should be processed.
     *
     * @return array
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
        $data = $this->getBaseData($model, $targetApiVersion);

        // Resolve each step
        $steps = array_map(function (string $step) {
            return Container::getInstance()->make($step);
        }, $this->steps());

        foreach ($steps as $step) {
            if ($this->shouldRunStep($step, $targetApiVersion)) {
                $data = $step->transform($data, $model, $targetApiVersion);
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

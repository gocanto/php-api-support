<?php
declare(strict_types=1);

namespace Tests\Http\Transformers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use YQueue\ApiSupport\Http\Transformers\AbstractTransformer;
use YQueue\ApiSupport\Versioning\ApiVersion;

class AbstractTransformerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testPassingANullModelReturnsNull()
    {
        $this->assertNull(
            $this->getTransformer()->transformModel(null, new ApiVersion('01-01-2020'))
        );
    }

    public function testValidModelGetsBaseData(): void
    {
        $model = Mockery::mock(Model::class)
            ->shouldReceive('toArray')->once()->andReturn(['foo' => 'bar'])
            ->getMock();

        $result = $this->getTransformer()->transformModel($model, new ApiVersion('01-01-2020'));

        $this->assertEquals(['foo' => 'bar'], $result);
    }

    public function testEmptyCollectionReturnsEmptyArray(): void
    {
        $this->assertEmpty(
            $this->getTransformer()->transformCollection([], new ApiVersion('01-01-2020'))
        );
    }

    public function testValidCollectionIsIteratedAndCallsGetBaseData(): void
    {
        $model = Mockery::mock(Model::class)
            ->shouldReceive('toArray')->times(3)->andReturn(['foo' => 'bar'])
            ->getMock();

        $collection = new Collection([
            $model,
            $model,
            $model
        ]);

        $result = $this->getTransformer()->transformCollection($collection, new ApiVersion('01-01-2020'));

        $this->assertEquals(array_fill(0, 3, ['foo' => 'bar']), $result);
    }

    public function testStepsAreOnlyRunWhenExpected(): void
    {
        $model = Mockery::mock(Model::class)
            ->shouldReceive('toArray')->andReturn(['foo' => 'bar'])
            ->getMock();

        $stepOne = Mockery::spy(TransformerStepStub::class, [new ApiVersion('02-01-2020')])->makePartial();
        $stepTwo = Mockery::spy(TransformerStepStub::class, [new ApiVersion('01-01-2020')])->makePartial();

        $this->getTransformer([$stepOne, $stepTwo])->transformModel($model, new ApiVersion('02-01-2020'));

        $stepOne->shouldHaveReceived('transform')->once();
        $stepTwo->shouldNotHaveReceived('transform');
    }

    public function testStepIsPassedMutatedDataFromPreviousStep(): void
    {
        $model = Mockery::mock(Model::class)
            ->shouldReceive('toArray')->andReturn(['foo' => 'bar'])
            ->getMock();

        $stepOne = Mockery::spy(TransformerStepStub::class, [new ApiVersion('02-01-2020'), ['step' => 'one']])->makePartial();
        $stepTwo = Mockery::spy(TransformerStepStub::class, [new ApiVersion('02-01-2020')])->makePartial();

        $this->getTransformer([$stepOne, $stepTwo])->transformModel($model, new ApiVersion('02-01-2020'));

        $stepOne->shouldHaveReceived('transform')->with(['foo' => 'bar'], $model)->once();
        $stepTwo->shouldHaveReceived('transform')->with(['foo' => 'bar', 'step' => 'one'], $model)->once();
    }

    /**
     * Retrieve a transformer for the tests, optionally supplying some steps to process.
     *
     * @param array $steps
     * @return AbstractTransformer
     */
    private function getTransformer($steps = []): AbstractTransformer
    {
        return new class($steps) extends AbstractTransformer {
            private $expectedSteps;

            public function __construct($steps)
            {
                $this->expectedSteps = $steps;
            }

            protected function getBaseData($model): array
            {
                if ($model instanceof Model) {
                    return $model->toArray();
                } else {
                    return (array)$model;
                }
            }

            protected function steps(): array
            {
                return $this->expectedSteps;
            }
        };
    }
}

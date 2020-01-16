<?php
declare(strict_types=1);

namespace Tests\Http\Transformers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Tests\Http\Transformers\Stubs\StepA;
use Tests\Http\Transformers\Stubs\StepB;
use Tests\Http\Transformers\Stubs\Transformer;
use YQueue\ApiSupport\Versioning\ApiVersion;

class AbstractTransformerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        StepTracker::getInstance()->reset();
    }

    public function testPassingANullModelReturnsNull()
    {
        $this->assertNull(
            (new Transformer)->transformModel(null, new ApiVersion('01-01-2020'))
        );
    }

    public function testValidModelGetsBaseData(): void
    {
        $model = Mockery::mock(Model::class)
            ->shouldReceive('toArray')->once()->andReturn(['foo' => 'bar'])
            ->getMock();

        $result = (new Transformer)->transformModel($model, new ApiVersion('01-01-2020'));

        $this->assertEquals(['foo' => 'bar'], $result);
    }

    public function testEmptyCollectionReturnsEmptyArray(): void
    {
        $this->assertEmpty(
            (new Transformer)->transformCollection([], new ApiVersion('01-01-2020'))
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

        $result = (new Transformer)->transformCollection($collection, new ApiVersion('01-01-2020'));

        $this->assertEquals(array_fill(0, 3, ['foo' => 'bar']), $result);
    }

    public function testStepsAreOnlyRunWhenExpected(): void
    {
        $model = Mockery::mock(Model::class)
            ->shouldReceive('toArray')->andReturn(['foo' => 'bar'])
            ->getMock();

        $transformer = new Transformer([
            StepA::class,
            StepB::class,
        ]);

        $transformer->transformModel($model, new ApiVersion('02-01-2020'));

        $this->assertEquals(1, StepTracker::getInstance()->count(StepA::class));
        $this->assertEquals(0, StepTracker::getInstance()->count(StepB::class));
    }

    public function testStepIsPassedMutatedDataFromPreviousStep(): void
    {
        $model = Mockery::mock(Model::class)
            ->shouldReceive('toArray')->andReturn(['foo' => 'bar'])
            ->getMock();

        $transformer = new Transformer([
            StepA::class,
            StepB::class,
        ]);

        $expected = [
            'foo' => 'bar',
            'step_a' => true,
            'step_b' => true,
        ];

        $actual = $transformer->transformModel($model, new ApiVersion('01-01-2020'));

        $this->assertEquals($expected, $actual);
    }
}

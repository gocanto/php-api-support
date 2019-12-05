<?php
declare(strict_types=1);

namespace Tests\Rules;

use PHPUnit\Framework\TestCase;
use YQueue\ApiSupport\Rules\AbstractRuleWithError;

final class AbstractRuleWithErrorTest extends TestCase
{
    const DEFAULT_MESSAGE = 'This is a default message.';
    const CUSTOM_MESSAGE = 'This is a custom message.';

    public function testFailWithoutMessageReturnsDefault(): void
    {
        $rule = new class extends AbstractRuleWithError {

            public static function defaultMessage(): string
            {
                return AbstractRuleWithErrorTest::DEFAULT_MESSAGE;
            }

            public function passes($attribute, $value)
            {
                return false;
            }
        };

        $rule->passes('attribute', 'value');
        $this->assertSame(AbstractRuleWithErrorTest::DEFAULT_MESSAGE, $rule->message());
    }

    public function testFailMethodSetsMessage(): void
    {
        $rule = new class extends AbstractRuleWithError {

            public static function defaultMessage(): string
            {
                return AbstractRuleWithErrorTest::DEFAULT_MESSAGE;
            }

            public function passes($attribute, $value)
            {
                return $this->fail(AbstractRuleWithErrorTest::CUSTOM_MESSAGE);
            }
        };

        $rule->passes('attribute', 'value');
        $this->assertSame(AbstractRuleWithErrorTest::CUSTOM_MESSAGE, $rule->message());
    }
}

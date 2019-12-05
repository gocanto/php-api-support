<?php

declare(strict_types=1);

namespace YQueue\ApiSupport\Rules;

use Illuminate\Contracts\Validation\Rule;

abstract class AbstractRuleWithError implements Rule
{
    /**
     * The message that should be displayed by the validator if the validation rule fails.
     *
     * @var string|null
     */
    protected $message;

    /**
     * Convenience method to set the error message and return false to the validator.
     *
     * @param string|null $message
     * @return bool
     */
    public function fail($message = null): bool
    {
        $this->message = $message;

        return false;
    }

    /**
     * Accessor for the validator.
     *
     * @return string
     */
    public function message(): string
    {
        return $this->message ?? static::defaultMessage();
    }

    /**
     * The default message that should be returned if another message is not explicitly set.
     *
     * @return string
     */
    abstract public static function defaultMessage(): string;
}

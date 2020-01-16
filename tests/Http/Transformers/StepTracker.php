<?php

namespace Tests\Http\Transformers;

final class StepTracker
{
    /**
     * @var StepTracker
     */
    private static $instance;

    /**
     * @var array
     */
    private $resolved;

    /**
     * StepTracker constructor.
     */
    private function __construct()
    {
        $this->resolved = [];
    }

    /**
     * Get the globally available instance of the container.
     *
     * @return static
     */
    public static function getInstance(): StepTracker
    {
        if (static::$instance === null) {
            static::$instance = new StepTracker;
        }

        return static::$instance;
    }

    /**
     * Reset our tracker.
     */
    public function reset(): void
    {
        $this->resolved = [];
    }

    /**
     * @param string $name
     */
    public function track(string $name): void
    {
        if (! array_key_exists($name, $this->resolved)) {
            $this->resolved[$name] = 0;
        }

        $this->resolved[$name] += 1;
    }

    /**
     * @param string $name
     * @return int
     */
    public function count(string $name): int
    {
        return $this->resolved[$name] ?? 0;
    }
}

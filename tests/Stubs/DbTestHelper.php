<?php

declare(strict_types=1);

namespace Tests\Stubs;

use Darkheim\Infrastructure\Database\dB;

/**
 * Helper trait for tests that need to bypass the DB constructor.
 * Usage: call injectDb($class, $mockDb) instead of `new $class()`.
 */
trait DbTestHelper
{
    /**
     * Creates an instance of $className WITHOUT calling its constructor,
     * then injects a mock dB into the given property name.
     */
    protected function makeWithDb(string $className, dB $mockDb, string $prop = 'muonline'): object
    {
        $instance = new \ReflectionClass($className)->newInstanceWithoutConstructor();
        $rProp    = new \ReflectionProperty($className, $prop);
        $rProp->setValue($instance, $mockDb);
        return $instance;
    }

    /**
     * Sets any additional property via reflection.
     */
    protected function setProp(object $instance, string $prop, mixed $value): void
    {
        $rProp = new \ReflectionProperty($instance, $prop);
        $rProp->setValue($instance, $value);
    }
}

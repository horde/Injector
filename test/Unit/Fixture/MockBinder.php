<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit\Fixture;

use Horde\Injector\Binder;
use Horde\Injector\Injector;

/**
 * Mock binder used in tests - returns the injector itself
 */
class MockBinder implements Binder
{
    public function create(Injector $injector): Injector
    {
        return $injector;
    }

    public function equals(Binder $otherBinder): bool
    {
        return $otherBinder === $this;
    }
}

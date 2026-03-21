<?php
declare(strict_types=1);

namespace Horde\Injector\Test\Unit\Fixture;

use Horde\Injector\Binder;
use Horde\Injector\Injector;

/**
 * Mock binder with constructor dependencies for testing
 */
class MockBinderWithDependencies implements Binder
{
    public function __construct(
        private readonly mixed $parameter1
    ) {
    }

    public function create(Injector $injector): Injector
    {
        return $injector;
    }

    public function equals(Binder $otherBinder): bool
    {
        return $otherBinder === $this;
    }
}

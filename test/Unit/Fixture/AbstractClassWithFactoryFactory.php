<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit\Fixture;

use Horde\Injector\Injector;

class AbstractClassWithFactoryFactory
{
    public function create(Injector $injector): AbstractClassWithFactory
    {
        return new ConcreteSubclass();
    }
}

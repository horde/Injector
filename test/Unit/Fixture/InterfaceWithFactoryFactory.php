<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit\Fixture;

use Horde\Injector\Injector;

class InterfaceWithFactoryFactory
{
    public function create(Injector $injector): InterfaceWithFactory
    {
        return new InterfaceWithFactoryImpl();
    }
}

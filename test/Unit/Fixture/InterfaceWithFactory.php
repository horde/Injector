<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit\Fixture;

use Horde\Injector\Attribute\Factory;

#[Factory(factory: InterfaceWithFactoryFactory::class, method: 'create')]
interface InterfaceWithFactory
{
    public function getSource(): string;
}

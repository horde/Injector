<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit\Fixture;

use Horde\Injector\Attribute\Factory;

#[Factory(factory: AbstractClassWithFactoryFactory::class, method: 'create')]
abstract class AbstractClassWithFactory
{
    abstract public function getSource(): string;
}

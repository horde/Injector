<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit\Fixture;

class ConcreteSubclass extends AbstractClassWithFactory
{
    public function getSource(): string
    {
        return 'factory-created';
    }
}

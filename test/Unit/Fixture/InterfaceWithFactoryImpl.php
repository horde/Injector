<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit\Fixture;

class InterfaceWithFactoryImpl implements InterfaceWithFactory
{
    public function getSource(): string
    {
        return 'factory-created';
    }
}

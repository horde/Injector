<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit\Fixture;

class ClassWithAllBuiltinUnion
{
    public function __construct(
        private readonly string|int $dep = 'default',
    ) {}

    public function getDep(): string|int
    {
        return $this->dep;
    }
}

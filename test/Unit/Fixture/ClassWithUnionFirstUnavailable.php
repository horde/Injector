<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit\Fixture;

class ClassWithUnionFirstUnavailable
{
    public function __construct(
        private readonly AnInterface|ClassImplementingAnInterface $dep,
    ) {}

    public function getDep(): AnInterface|ClassImplementingAnInterface
    {
        return $this->dep;
    }
}

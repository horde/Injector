<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit\Fixture;

class ClassDependingOnUnionType
{
    public function __construct(
        private readonly ClassImplementingAnInterface|ClassWithOptionalStringDefaultParam $dep,
    ) {}

    public function getDep(): ClassImplementingAnInterface|ClassWithOptionalStringDefaultParam
    {
        return $this->dep;
    }
}

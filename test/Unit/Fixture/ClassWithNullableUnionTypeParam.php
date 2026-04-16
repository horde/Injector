<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit\Fixture;

class ClassWithNullableUnionTypeParam
{
    public function __construct(
        private readonly AnInterface|null $dep = null,
    ) {}

    public function getDep(): ?AnInterface
    {
        return $this->dep;
    }
}

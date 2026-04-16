<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit\Fixture;

class ClassDependingOnEnum
{
    public function __construct(
        private readonly SampleEnum $dep,
    ) {}

    public function getDep(): SampleEnum
    {
        return $this->dep;
    }
}

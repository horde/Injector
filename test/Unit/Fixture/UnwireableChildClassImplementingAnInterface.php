<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit\Fixture;

class UnwireableChildClassImplementingAnInterface extends ClassImplementingAnInterface
{
    public function __construct(
        private readonly string $noDefaults
    ) {}

    public function getNoDefaults(): string
    {
        return $this->noDefaults;
    }
}

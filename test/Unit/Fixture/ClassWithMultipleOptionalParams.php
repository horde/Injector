<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit\Fixture;

class ClassWithMultipleOptionalParams
{
    public function __construct(
        private readonly string $a = 'x',
        private readonly int $b = 0,
        private readonly ?array $c = null,
    ) {}

    public function getA(): string
    {
        return $this->a;
    }

    public function getB(): int
    {
        return $this->b;
    }

    public function getC(): ?array
    {
        return $this->c;
    }
}

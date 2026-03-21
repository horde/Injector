<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit\Fixture;

class ClassWithOptionalStringDefaultParam
{
    private string $optional;

    public function __construct(string $optional = 'foo')
    {
        $this->optional = $optional;
    }

    public function getParam(): string
    {
        return $this->optional;
    }
}

<?php
declare(strict_types=1);

namespace Horde\Injector\Test\Unit\Fixture;

class ClassWithOptionalStringNullParam
{
    private ?string $optional;

    public function __construct(?string $optional = null)
    {
        $this->optional = $optional;
    }

    public function getParam(): ?string
    {
        return $this->optional;
    }
}

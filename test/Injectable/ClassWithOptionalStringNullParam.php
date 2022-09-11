<?php

namespace Horde\Injector\Test\Injectable;

class ClassWithOptionalStringNullParam
{
    public function __construct(?string $optional = null)
    {
        $this->optional = $optional;
    }
    public function getParam(): ?string
    {
        return $this->optional;
    }
}

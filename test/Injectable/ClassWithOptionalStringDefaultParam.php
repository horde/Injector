<?php

namespace Horde\Injector\Test\Injectable;

class ClassWithOptionalStringDefaultParam
{
    public function __construct(string $optional = 'foo')
    {
        $this->optional = $optional;
    }
    public function getParam(): string
    {
        return $this->optional;
    }
}

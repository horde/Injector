<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit\Fixture;

class ClassWithArrayParam
{
    private ?array $param;

    public function __construct(?array $param = null)
    {
        $this->param = $param;
    }

    public function getParam(): ?array
    {
        return $this->param;
    }
}

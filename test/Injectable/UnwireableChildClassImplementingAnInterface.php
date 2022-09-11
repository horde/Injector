<?php
declare(strict_types=1);
namespace Horde\Injector\Test\Injectable;

class UnwireableChildClassImplementingAnInterface  extends ClassImplementingAnInterface
{
    public function __construct(private string $noDefaults)
    {
    }
    public function getNoDefaults()
    {
        return $this->noDefaults;
    }
}
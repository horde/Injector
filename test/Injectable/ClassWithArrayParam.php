<?php
declare(strict_types=1);
namespace Horde\Injector\Test\Injectable;

class ClassWithArrayParam
{
    public function __construct(array $param = null)
    {
        $this->param = $param;
    }
    public function getParam()
    {
        return $this->param;
    }
}

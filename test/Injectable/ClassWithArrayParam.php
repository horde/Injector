<?php
namespace Horde\Injector\Test\Injectable;
use Horde\Injector\Binder;
use Horde\Injector\DependencyFinder;
use Horde\Injector\Injector;
use Horde\Injector\TopLevel;
use BadMethodCallException;
use Horde\Injector\Binder\AnnotatedSetters;
use Horde\Injector\Binder\Mock;
use Horde\Injector\Binder\MockWithDependencies;
use Horde\Injector\Binder\Implementation;
use Horde\Injector\Binder\Factory;

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
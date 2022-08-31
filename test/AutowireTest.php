<?php

namespace Horde\Injector\Test;

use BadMethodCallException;
use Horde\Injector\Binder;
use Horde\Injector\Binder\AnnotatedSetters;
use Horde\Injector\Binder\Factory;
use Horde\Injector\Binder\Implementation;
use Horde\Injector\Binder\Mock;
use Horde\Injector\Binder\MockWithDependencies;
use Horde\Injector\DependencyFinder;
use Horde\Injector\Injector;
use Horde\Injector\Test\Injectable\ClassWithArrayParam;
use Horde\Injector\TopLevel;
use PHPUnit\Framework\TestCase;

class AutowireTest extends TestCase
{
    public function testAutowiringArrayDefaultNullShouldProvideNull()
    {
        $injector = new Injector(new TopLevel());
        $res = $injector->getInstance(ClassWithArrayParam::class);
        $this->assertNull($res->getParam());
    }
}

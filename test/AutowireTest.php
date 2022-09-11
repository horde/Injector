<?php

namespace Horde\Injector\Test;

use BadMethodCallException;
use Horde\Exception\NotFound;
use Horde\Injector\Binder;
use Horde\Injector\Binder\AnnotatedSetters;
use Horde\Injector\Binder\Factory;
use Horde\Injector\Binder\Implementation;
use Horde\Injector\Binder\Mock;
use Horde\Injector\Binder\MockWithDependencies;
use Horde\Injector\DependencyFinder;
use Horde\Injector\Injector;
use Horde\Injector\NotFoundException;
use Horde\Injector\Test\Injectable\AnInterface;
use Horde\Injector\Test\Injectable\ClassImplementingAnInterface;
use Horde\Injector\Test\Injectable\ClassWithArrayParam;
use Horde\Injector\Test\Injectable\ClassWithOptionalStringNullParam;
use Horde\Injector\Test\Injectable\ClassWithOptionalStringDefaultParam;
use Horde\Injector\TopLevel;
use PHPUnit\Framework\TestCase;

class AutowireTest extends TestCase
{
    public function testAutowiringArrayDefaultNullShouldProvideNull()
    {
        $injector = new Injector(new TopLevel());
        $res = $injector->getInstance(ClassWithArrayParam::class);
        $this->assertNull($res->getParam());
        $res = $injector->get(ClassWithArrayParam::class);
        $this->assertNull($res->getParam());
    }

    public function testAutowiringStringDefaultNullShouldProvideNull()
    {
        $injector = new Injector(new TopLevel());
        $res = $injector->getInstance(ClassWithOptionalStringNullParam::class);
        $this->assertNull($res->getParam());
        $res = $injector->get(ClassWithOptionalStringNullParam::class);
        $this->assertNull($res->getParam());
    }

    public function testAutowiringStringDefaultShouldProvideDefault()
    {
        $injector = new Injector(new TopLevel());
        $res = $injector->get(ClassWithOptionalStringDefaultParam::class);
        $this->assertEquals('foo', $res->getParam());
    }

    public function testJustProduceClassWithNoDependenciesExplicitly()
    {
        $injector = new Injector(new TopLevel());
//        $this->assertTrue($injector->has(ClassImplementingAnInterface::class));
        $res = $injector->get(ClassImplementingAnInterface::class);
        $this->assertInstanceOf(ClassImplementingAnInterface::class, $res);
    }

    public function testCannotProduceInterfaceWithoutRegistering()
    {
        $injector = new Injector(new TopLevel());
        $this->assertFalse($injector->has(AnInterface::class));
/*        $this->expectException(NotFoundException::class);
        $res = $injector->get(AnInterface::class);*/
    }

    public function testFailWithoutDefault()
    {

    }
}

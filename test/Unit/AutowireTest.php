<?php
declare(strict_types=1);

namespace Horde\Injector\Test\Unit;

use Horde\Injector\Injector;
use Horde\Injector\NotFoundException;
use Horde\Injector\Test\Unit\Fixture\AnInterface;
use Horde\Injector\Test\Unit\Fixture\ClassImplementingAnInterface;
use Horde\Injector\Test\Unit\Fixture\ClassWithArrayParam;
use Horde\Injector\Test\Unit\Fixture\ClassWithOptionalStringDefaultParam;
use Horde\Injector\Test\Unit\Fixture\ClassWithOptionalStringNullParam;
use Horde\Injector\Test\Unit\Fixture\UnwireableChildClassImplementingAnInterface;
use Horde\Injector\TopLevel;
use PHPUnit\Framework\TestCase;

class AutowireTest extends TestCase
{
    public function testAutowiringArrayDefaultNullShouldProvideNull(): void
    {
        $injector = new Injector(new TopLevel());
        $res = $injector->getInstance(ClassWithArrayParam::class);
        $this->assertNull($res->getParam());
        $res = $injector->get(ClassWithArrayParam::class);
        $this->assertNull($res->getParam());
    }

    public function testAutowiringStringDefaultNullShouldProvideNull(): void
    {
        $injector = new Injector(new TopLevel());
        $res = $injector->getInstance(ClassWithOptionalStringNullParam::class);
        $this->assertNull($res->getParam());
        $res = $injector->get(ClassWithOptionalStringNullParam::class);
        $this->assertNull($res->getParam());
    }

    public function testAutowiringStringDefaultShouldProvideDefault(): void
    {
        $injector = new Injector(new TopLevel());
        $res = $injector->get(ClassWithOptionalStringDefaultParam::class);
        $this->assertEquals('foo', $res->getParam());
    }

    public function testJustProduceClassWithNoDependenciesExplicitly(): void
    {
        $injector = new Injector(new TopLevel());
        // hasInstance is about having produced or assigned, not about being able to produce
        $this->assertFalse($injector->hasInstance(ClassImplementingAnInterface::class));
        $this->assertTrue($injector->has(ClassImplementingAnInterface::class));
        $res = $injector->get(ClassImplementingAnInterface::class);
        $this->assertInstanceOf(ClassImplementingAnInterface::class, $res);
        // We must have it after a successful get
        $this->assertTrue($injector->hasInstance(ClassImplementingAnInterface::class));
        $this->assertTrue($injector->has(ClassImplementingAnInterface::class));
    }

    public function testCannotProduceInterfaceWithoutRegistering(): void
    {
        $injector = new Injector(new TopLevel());
        $this->assertFalse($injector->has(AnInterface::class));
        $this->assertFalse($injector->hasInstance(AnInterface::class));
        $this->expectException(NotFoundException::class);
        $res = $injector->get(AnInterface::class);
    }

    public function testFailWithoutDefault(): void
    {
        $injector = new Injector(new TopLevel());
        $this->assertFalse($injector->has(UnwireableChildClassImplementingAnInterface::class));
        $this->assertFalse($injector->hasInstance(UnwireableChildClassImplementingAnInterface::class));
        $this->expectException(NotFoundException::class);
        $res = $injector->get(UnwireableChildClassImplementingAnInterface::class);
    }
}

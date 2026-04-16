<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit;

use Horde\Injector\Injector;
use Horde\Injector\NotFoundException;
use Horde\Injector\Test\Unit\Fixture\ClassDependingOnEnum;
use Horde\Injector\Test\Unit\Fixture\SampleEnum;
use Horde\Injector\Test\Unit\Fixture\SampleUnitEnum;
use Horde\Injector\TopLevel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Injector::class)]
class EnumHandlingTest extends TestCase
{
    /**
     * class_exists() returns true for enums and the enum has no constructor,
     * so has() takes the 'noConstructor' path and returns true.
     * However get() will fail because enums are not instantiable.
     * This documents a PSR-11 contract violation.
     */
    public function testHasReturnsTrueForEnum(): void
    {
        $injector = new Injector(new TopLevel());
        $this->assertTrue($injector->has(SampleEnum::class));
    }

    /**
     * Enums pass has() but cannot be instantiated via reflection.
     * get() throws NotFoundException.
     * Documents PSR-11 violation: has()=true but get() throws.
     */
    public function testGetThrowsForEnum(): void
    {
        $injector = new Injector(new TopLevel());
        $this->expectException(NotFoundException::class);
        $injector->get(SampleEnum::class);
    }

    /**
     * Unit enums (no backing type) have the same PSR-11 violation.
     */
    public function testGetThrowsForUnitEnum(): void
    {
        $injector = new Injector(new TopLevel());
        $this->assertTrue($injector->has(SampleUnitEnum::class));
        $this->expectException(NotFoundException::class);
        $injector->get(SampleUnitEnum::class);
    }

    /**
     * Manually registered enum instances work fine.
     */
    public function testSetInstanceWorksForEnum(): void
    {
        $injector = new Injector(new TopLevel());
        $injector->setInstance(SampleEnum::class, SampleEnum::Foo);
        $this->assertSame(SampleEnum::Foo, $injector->get(SampleEnum::class));
    }

    /**
     * has() returns true after setInstance for an enum.
     */
    public function testHasReturnsTrueAfterSetInstanceForEnum(): void
    {
        $injector = new Injector(new TopLevel());
        $injector->setInstance(SampleEnum::class, SampleEnum::Bar);
        $this->assertTrue($injector->has(SampleEnum::class));
    }

    /**
     * A class depending on an enum fails when the enum is not registered,
     * because the enum cannot be autowired as a dependency.
     */
    public function testClassDependingOnEnumFailsWithoutBinding(): void
    {
        $injector = new Injector(new TopLevel());
        $this->expectException(NotFoundException::class);
        $injector->get(ClassDependingOnEnum::class);
    }

    /**
     * After registering an enum instance, a class depending on it succeeds.
     */
    public function testClassDependingOnEnumSucceedsWithSetInstance(): void
    {
        $injector = new Injector(new TopLevel());
        $injector->setInstance(SampleEnum::class, SampleEnum::Foo);
        $result = $injector->get(ClassDependingOnEnum::class);
        $this->assertInstanceOf(ClassDependingOnEnum::class, $result);
        $this->assertSame(SampleEnum::Foo, $result->getDep());
    }
}

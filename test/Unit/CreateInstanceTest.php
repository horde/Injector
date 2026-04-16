<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit;

use Horde\Injector\Injector;
use Horde\Injector\Test\Unit\Fixture\ClassImplementingAnInterface;
use Horde\Injector\TopLevel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(Injector::class)]
class CreateInstanceTest extends TestCase
{
    /**
     * createInstance() always creates a new object.
     */
    public function testCreateInstanceReturnsNewInstanceEachCall(): void
    {
        $injector = new Injector(new TopLevel());
        $a = $injector->createInstance(stdClass::class);
        $b = $injector->createInstance(stdClass::class);
        $this->assertNotSame($a, $b);
    }

    /**
     * createInstance() does NOT store the result in the instance cache.
     */
    public function testCreateInstanceDoesNotPopulateCache(): void
    {
        $injector = new Injector(new TopLevel());
        $injector->createInstance(ClassImplementingAnInterface::class);
        $this->assertFalse($injector->hasInstance(ClassImplementingAnInterface::class));
    }

    /**
     * getInstance() (via get()) caches and returns the same object on
     * subsequent calls.
     */
    public function testGetInstanceCachesSameObject(): void
    {
        $injector = new Injector(new TopLevel());
        $a = $injector->getInstance(ClassImplementingAnInterface::class);
        $b = $injector->getInstance(ClassImplementingAnInterface::class);
        $this->assertSame($a, $b);
        $this->assertTrue($injector->hasInstance(ClassImplementingAnInterface::class));
    }
}

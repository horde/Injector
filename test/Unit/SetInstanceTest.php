<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit;

use Horde\Injector\Injector;
use Horde\Injector\Test\Unit\Fixture\AnInterface;
use Horde\Injector\Test\Unit\Fixture\ClassImplementingAnInterface;
use Horde\Injector\TopLevel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(Injector::class)]
class SetInstanceTest extends TestCase
{
    public function testSetInstanceReturnsInjectorForChaining(): void
    {
        $injector = new Injector(new TopLevel());
        $result = $injector->setInstance('key', new stdClass());
        $this->assertSame($injector, $result);
    }

    public function testSetInstanceOverwritesExisting(): void
    {
        $injector = new Injector(new TopLevel());
        $first = new stdClass();
        $second = new stdClass();
        $injector->setInstance('key', $first);
        $injector->setInstance('key', $second);
        $this->assertSame($second, $injector->get('key'));
        $this->assertNotSame($first, $injector->get('key'));
    }

    public function testSetInstanceStoresNonObjectValue(): void
    {
        $injector = new Injector(new TopLevel());
        $injector->setInstance('config.debug', true);
        $injector->setInstance('config.name', 'test');
        $injector->setInstance('config.count', 42);

        $this->assertTrue($injector->get('config.debug'));
        $this->assertSame('test', $injector->get('config.name'));
        $this->assertSame(42, $injector->get('config.count'));
    }

    public function testSetInstanceInvalidatesHasNotCache(): void
    {
        $injector = new Injector(new TopLevel());
        // First: has() returns false, enters hasNotCache
        $this->assertFalse($injector->has(AnInterface::class));
        // setInstance removes from hasNotCache and updates hasCache
        $injector->setInstance(AnInterface::class, new ClassImplementingAnInterface());
        // Now has() returns true
        $this->assertTrue($injector->has(AnInterface::class));
    }
}

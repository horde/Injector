<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit;

use Horde\Injector\BindingMapCollector;
use Horde\Injector\Event\BindingRegistered;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(BindingMapCollector::class)]
class BindingMapCollectorTest extends TestCase
{
    public function testCollectsImplementationBinding(): void
    {
        $collector = new BindingMapCollector();
        $collector(new BindingRegistered('FooInterface', 'Implementation', 'ConcreteFoo'));

        $bindings = $collector->getBindings();
        $this->assertArrayHasKey('FooInterface', $bindings);
        $this->assertSame('Implementation', $bindings['FooInterface']['type']);
        $this->assertSame('ConcreteFoo', $bindings['FooInterface']['implementation']);
    }

    public function testCollectsFactoryBinding(): void
    {
        $collector = new BindingMapCollector();
        $collector(new BindingRegistered('BarInterface', 'Factory', 'BarFactory'));

        $bindings = $collector->getBindings();
        $this->assertSame('Factory', $bindings['BarInterface']['type']);
        $this->assertSame('BarFactory', $bindings['BarInterface']['implementation']);
    }

    public function testFlagsClosureAsUncacheable(): void
    {
        $collector = new BindingMapCollector();
        $collector(new BindingRegistered('BazInterface', 'Closure', null));

        $this->assertSame(['BazInterface'], $collector->getUncacheable());
    }

    public function testResetClearsState(): void
    {
        $collector = new BindingMapCollector();
        $collector(new BindingRegistered('FooInterface', 'Implementation', 'ConcreteFoo'));
        $collector(new BindingRegistered('BarInterface', 'Closure', null));

        $collector->reset();

        $this->assertSame([], $collector->getBindings());
        $this->assertSame([], $collector->getUncacheable());
    }

    public function testIgnoresUnrelatedEvents(): void
    {
        $collector = new BindingMapCollector();
        $collector(new stdClass());

        $this->assertSame([], $collector->getBindings());
    }
}

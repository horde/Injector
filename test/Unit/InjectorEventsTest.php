<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit;

use Horde\Injector\Binder\Implementation;
use Horde\Injector\Event\BindingRegistered;
use Horde\Injector\Event\DependencyCreated;
use Horde\Injector\Event\DependencyResolved;
use Horde\Injector\Injector;
use Horde\Injector\TopLevel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use stdClass;

#[CoversClass(Injector::class)]
class InjectorEventsTest extends TestCase
{
    public function testNoDispatcherNoEvents(): void
    {
        $injector = new Injector(new TopLevel());
        $injector->addBinder('foo', new Implementation(stdClass::class));
        $injector->get(stdClass::class);
        // No exception — zero overhead when no dispatcher set
        $this->assertTrue(true);
    }

    public function testSetEventDispatcherReturnsSelf(): void
    {
        $injector = new Injector(new TopLevel());
        $result = $injector->setEventDispatcher(new CapturingDispatcher());
        $this->assertSame($injector, $result);
    }

    public function testBindingRegisteredFired(): void
    {
        $injector = new Injector(new TopLevel());
        $dispatcher = new CapturingDispatcher();
        $injector->setEventDispatcher($dispatcher);

        $injector->addBinder('some.interface', new Implementation(stdClass::class));

        $events = $dispatcher->getEventsOfType(BindingRegistered::class);
        $this->assertCount(1, $events);
        $this->assertSame('some.interface', $events[0]->interface);
        $this->assertSame('Implementation', $events[0]->binderType);
        $this->assertSame(stdClass::class, $events[0]->implementation);
    }

    public function testDependencyResolvedFiredOnCreation(): void
    {
        $injector = new Injector(new TopLevel());
        $dispatcher = new CapturingDispatcher();
        $injector->setEventDispatcher($dispatcher);

        $injector->get(stdClass::class);

        $events = $dispatcher->getEventsOfType(DependencyResolved::class);
        $this->assertCount(1, $events);
        $this->assertSame(stdClass::class, $events[0]->interface);
        $this->assertFalse($events[0]->fromCache);
        $this->assertSame('created', $events[0]->source);
    }

    public function testDependencyResolvedFiredOnCacheHit(): void
    {
        $injector = new Injector(new TopLevel());
        $dispatcher = new CapturingDispatcher();

        // First call without dispatcher to populate cache
        $injector->get(stdClass::class);

        // Now enable dispatcher
        $injector->setEventDispatcher($dispatcher);
        $injector->get(stdClass::class);

        $events = $dispatcher->getEventsOfType(DependencyResolved::class);
        $this->assertCount(1, $events);
        $this->assertTrue($events[0]->fromCache);
        $this->assertSame('cache', $events[0]->source);
    }

    public function testDependencyCreatedFiredWithTiming(): void
    {
        $injector = new Injector(new TopLevel());
        $dispatcher = new CapturingDispatcher();
        $injector->setEventDispatcher($dispatcher);

        $injector->createInstance(stdClass::class);

        $events = $dispatcher->getEventsOfType(DependencyCreated::class);
        $this->assertCount(1, $events);
        $this->assertSame(stdClass::class, $events[0]->interface);
        $this->assertSame('Implementation', $events[0]->binderType);
        $this->assertGreaterThanOrEqual(0.0, $events[0]->durationMs);
    }

    public function testClearDispatcherStopsEvents(): void
    {
        $injector = new Injector(new TopLevel());
        $dispatcher = new CapturingDispatcher();
        $injector->setEventDispatcher($dispatcher);
        $injector->setEventDispatcher(null);

        $injector->addBinder('foo', new Implementation(stdClass::class));
        $injector->get(stdClass::class);

        $this->assertSame([], $dispatcher->events);
    }
}

class CapturingDispatcher implements EventDispatcherInterface
{
    /** @var object[] */
    public array $events = [];

    public function dispatch(object $event): object
    {
        $this->events[] = $event;
        return $event;
    }

    /** @return object[] */
    public function getEventsOfType(string $class): array
    {
        return array_values(array_filter(
            $this->events,
            fn(object $e) => $e instanceof $class,
        ));
    }
}

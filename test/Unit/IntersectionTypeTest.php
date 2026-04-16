<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit;

use Horde\Injector\DependencyFinder;
use Horde\Injector\Injector;
use Horde\Injector\NotFoundException;
use Horde\Injector\Test\Unit\Fixture\IntersectionInterface1;
use Horde\Injector\Test\Unit\Fixture\IntersectionInterface2;
use Horde\Injector\TopLevel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DependencyFinder::class)]
#[CoversClass(Injector::class)]
class IntersectionTypeTest extends TestCase
{
    /**
     * Required intersection type parameter cannot be resolved when no
     * implementation is registered. Throws NotFoundException.
     */
    public function testIntersectionTypeRequiredParamThrows(): void
    {
        $injector = new Injector(new TopLevel());
        $this->expectException(NotFoundException::class);
        $injector->get(NeedsIntersection::class);
    }

    /**
     * DNF type (Interface1&Interface2)|null with default null.
     * No implementation registered — intersection members unresolvable,
     * null is builtin and skipped, falls through to optional default.
     */
    public function testDnfTypeWithNullMemberFallsToDefault(): void
    {
        $injector = new Injector(new TopLevel());
        $result = $injector->get(NeedsDnfNullable::class);
        $this->assertInstanceOf(NeedsDnfNullable::class, $result);
        $this->assertNull($result->dep);
    }

    /**
     * When an instance implementing both interfaces is registered under
     * one of the member names, intersection resolution finds it and
     * verifies it satisfies all members.
     */
    public function testIntersectionResolvesWhenInstanceRegistered(): void
    {
        $injector = new Injector(new TopLevel());
        $impl = new BothInterfacesImpl();
        $injector->setInstance(IntersectionInterface1::class, $impl);
        $result = $injector->get(NeedsIntersection::class);
        $this->assertInstanceOf(NeedsIntersection::class, $result);
        $this->assertSame($impl, $result->dep);
    }

    /**
     * When a registered instance only satisfies one member of the
     * intersection, resolution fails and throws for required params.
     */
    public function testIntersectionRejectsPartialImplementation(): void
    {
        $injector = new Injector(new TopLevel());
        $injector->setInstance(IntersectionInterface1::class, new OnlyInterface1Impl());
        $this->expectException(NotFoundException::class);
        $injector->get(NeedsIntersection::class);
    }
}

class NeedsIntersection
{
    public function __construct(
        public readonly IntersectionInterface1&IntersectionInterface2 $dep,
    ) {}
}

class NeedsDnfNullable
{
    public function __construct(
        public readonly (IntersectionInterface1&IntersectionInterface2)|null $dep = null,
    ) {}
}

class BothInterfacesImpl implements IntersectionInterface1, IntersectionInterface2
{
    public function method1(): void {}
    public function method2(): void {}
}

class OnlyInterface1Impl implements IntersectionInterface1
{
    public function method1(): void {}
}

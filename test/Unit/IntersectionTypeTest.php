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
     * Required intersection type parameter cannot be resolved.
     * ReflectionIntersectionType is not ReflectionNamedType or
     * ReflectionUnionType, so DependencyFinder sets $types = [] (line 114),
     * the loop does nothing, and the code falls through to throw.
     */
    public function testIntersectionTypeRequiredParamThrows(): void
    {
        $injector = new Injector(new TopLevel());
        $this->expectException(NotFoundException::class);
        $injector->get(NeedsIntersection::class);
    }

    /**
     * DNF type (Interface1&Interface2)|null with default null.
     * This is a ReflectionUnionType containing a ReflectionIntersectionType
     * and a ReflectionNamedType('null'). The union loop skips the intersection
     * member (not ReflectionNamedType) but tries getInstance('null') on the
     * null member (it's not in the builtin skip list). This throws instead
     * of falling to the default.
     * Documents the DNF/null handling bug.
     */
    public function testDnfTypeWithNullMemberThrows(): void
    {
        $injector = new Injector(new TopLevel());
        $this->expectException(NotFoundException::class);
        $injector->get(NeedsDnfNullable::class);
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

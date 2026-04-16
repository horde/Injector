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
     * member (not ReflectionNamedType) and skips null (isBuiltin()=true).
     * Falls through to optional default → null.
     */
    public function testDnfTypeWithNullMemberFallsToDefault(): void
    {
        $injector = new Injector(new TopLevel());
        $result = $injector->get(NeedsDnfNullable::class);
        $this->assertInstanceOf(NeedsDnfNullable::class, $result);
        $this->assertNull($result->dep);
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

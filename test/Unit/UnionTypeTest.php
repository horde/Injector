<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit;

use Horde\Injector\DependencyFinder;
use Horde\Injector\Injector;
use Horde\Injector\NotFoundException;
use Horde\Injector\Test\Unit\Fixture\AnInterface;
use Horde\Injector\Test\Unit\Fixture\ClassDependingOnUnionType;
use Horde\Injector\Test\Unit\Fixture\ClassImplementingAnInterface;
use Horde\Injector\Test\Unit\Fixture\ClassWithAllBuiltinUnion;
use Horde\Injector\Test\Unit\Fixture\ClassWithNullableUnionTypeParam;
use Horde\Injector\Test\Unit\Fixture\ClassWithUnionFirstUnavailable;
use Horde\Injector\TopLevel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DependencyFinder::class)]
#[CoversClass(Injector::class)]
class UnionTypeTest extends TestCase
{
    /**
     * Both union members are concrete autowireable classes.
     * DependencyFinder resolves the first available member.
     */
    public function testUnionTypeResolvesFirstAvailableMember(): void
    {
        $injector = new Injector(new TopLevel());
        $result = $injector->get(ClassDependingOnUnionType::class);
        $this->assertInstanceOf(ClassDependingOnUnionType::class, $result);
        // First member ClassImplementingAnInterface is concrete and autowireable
        $this->assertInstanceOf(ClassImplementingAnInterface::class, $result->getDep());
    }

    /**
     * First union member is an unbound interface, second is concrete.
     * The try/catch in the union loop catches the first failure and
     * resolves the second member successfully.
     */
    public function testUnionTypeFallsToSecondWhenFirstUnavailable(): void
    {
        $injector = new Injector(new TopLevel());
        $result = $injector->get(ClassWithUnionFirstUnavailable::class);
        $this->assertInstanceOf(ClassWithUnionFirstUnavailable::class, $result);
        $this->assertInstanceOf(ClassImplementingAnInterface::class, $result->getDep());
    }

    /**
     * Nullable union: AnInterface|null with default null.
     * AnInterface is unbound, but the parameter is optional.
     * The named-type branch (DependencyFinder:92) catches the failure and
     * falls back to the default value.
     */
    public function testNullableUnionFallsToDefaultWhenNoneAvailable(): void
    {
        $injector = new Injector(new TopLevel());
        $result = $injector->get(ClassWithNullableUnionTypeParam::class);
        $this->assertInstanceOf(ClassWithNullableUnionTypeParam::class, $result);
        $this->assertNull($result->getDep());
    }

    /**
     * All union members are builtins (string|int).
     * DependencyFinder:118 skips them all, falls through to optional default.
     */
    public function testAllBuiltinUnionUsesDefault(): void
    {
        $injector = new Injector(new TopLevel());
        $result = $injector->get(ClassWithAllBuiltinUnion::class);
        $this->assertInstanceOf(ClassWithAllBuiltinUnion::class, $result);
        $this->assertSame('default', $result->getDep());
    }

    /**
     * Two unbound interface members, not optional → NotFoundException.
     */
    public function testUnionTypeThrowsWhenNoMemberResolvable(): void
    {
        $injector = new Injector(new TopLevel());
        $this->expectException(NotFoundException::class);
        $injector->get(UnionBothUnboundFixture::class);
    }

    /**
     * has() casts ReflectionUnionType to string (e.g. "A|B"), then calls
     * has("A|B") recursively. No class named "A|B" exists, so has() returns
     * false even though get() would succeed.
     * Documents PSR-11 contract violation.
     */
    public function testHasReturnsFalseForClassWithUnionParam(): void
    {
        $injector = new Injector(new TopLevel());

        // get() succeeds — both members are concrete
        $result = $injector->get(ClassDependingOnUnionType::class);
        $this->assertInstanceOf(ClassDependingOnUnionType::class, $result);

        // But on a fresh injector, has() returns false due to the string-cast bug
        $fresh = new Injector(new TopLevel());
        $this->assertFalse($fresh->has(ClassDependingOnUnionType::class));
    }
}

/**
 * Embedded fixture: both union members are unbound interfaces, not optional.
 */
class UnionBothUnboundFixture
{
    public function __construct(
        private readonly UnboundInterfaceA|UnboundInterfaceB $dep,
    ) {}
}

interface UnboundInterfaceA {}
interface UnboundInterfaceB {}

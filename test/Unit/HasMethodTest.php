<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit;

use Horde\Injector\Binder\Closure as ClosureBinder;
use Horde\Injector\Binder\Implementation;
use Horde\Injector\Injector;
use Horde\Injector\Test\Unit\Fixture\AnInterface;
use Horde\Injector\Test\Unit\Fixture\ClassDependingOnUnionType;
use Horde\Injector\Test\Unit\Fixture\ClassImplementingAnInterface;
use Horde\Injector\Test\Unit\Fixture\ClassWithMultipleOptionalParams;
use Horde\Injector\Test\Unit\Fixture\ClassWithOptionalStringDefaultParam;
use Horde\Injector\TopLevel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(Injector::class)]
class HasMethodTest extends TestCase
{
    // --- hasInstance path (line 547) ---

    public function testHasReturnsTrueForInjectorItself(): void
    {
        $injector = new Injector(new TopLevel());
        $this->assertTrue($injector->has(Injector::class));
    }

    public function testHasReturnsTrueAfterSetInstance(): void
    {
        $injector = new Injector(new TopLevel());
        $injector->setInstance('some.key', new stdClass());
        $this->assertTrue($injector->has('some.key'));
    }

    // --- explicit binding path (line 551) ---

    public function testHasReturnsTrueForExplicitBinding(): void
    {
        $injector = new Injector(new TopLevel());
        $injector->addBinder(
            AnInterface::class,
            new Implementation(ClassImplementingAnInterface::class)
        );
        $this->assertTrue($injector->has(AnInterface::class));
    }

    // --- parent delegation path (line 555) ---

    public function testHasReturnsTrueForParentBinding(): void
    {
        $parent = new Injector(new TopLevel());
        $parent->addBinder(
            AnInterface::class,
            new Implementation(ClassImplementingAnInterface::class)
        );
        $child = $parent->createChildInjector();
        $this->assertTrue($child->has(AnInterface::class));
    }

    // --- hasCache hit path (line 536) ---

    public function testHasCacheHitReturnsTrue(): void
    {
        $injector = new Injector(new TopLevel());
        $injector->setInstance('cached.key', new stdClass());
        // First call populates hasCache
        $this->assertTrue($injector->has('cached.key'));
        // Second call hits hasCache directly
        $this->assertTrue($injector->has('cached.key'));
    }

    // --- hasCache['parent'] re-delegation (line 538-539) ---

    public function testHasCacheParentRedelegates(): void
    {
        $parent = new Injector(new TopLevel());
        $parent->setInstance('parent.key', new stdClass());
        $child = $parent->createChildInjector();

        // First call: delegates to parent, caches as 'parent'
        $this->assertTrue($child->has('parent.key'));
        // Second call: hasCache['parent.key'] == 'parent', re-checks parent
        $this->assertTrue($child->has('parent.key'));
    }

    // --- hasNotCache hit path (line 543-546) ---

    public function testHasNotCacheStillChecksParent(): void
    {
        $parent = new Injector(new TopLevel());
        $child = $parent->createChildInjector();

        // First call on child — not found, cached in hasNotCache
        $this->assertFalse($child->has('nonexistent.key'));
        // Second call hits hasNotCache, re-checks parent (still false)
        $this->assertFalse($child->has('nonexistent.key'));

        // Now parent gains the instance — child's hasNotCache re-check finds it
        $parent->setInstance('nonexistent.key', new stdClass());
        $this->assertTrue($child->has('nonexistent.key'));
    }

    // --- hasNotCache invalidation via addBinder (lines 206-209) ---

    public function testHasNotCacheInvalidatedByAddBinder(): void
    {
        $injector = new Injector(new TopLevel());
        // Enters hasNotCache
        $this->assertFalse($injector->has(AnInterface::class));
        // addBinder removes from hasNotCache
        $injector->addBinder(
            AnInterface::class,
            new Implementation(ClassImplementingAnInterface::class)
        );
        $this->assertTrue($injector->has(AnInterface::class));
    }

    // --- hasNotCache invalidation via setInstance (lines 293-297) ---

    public function testHasNotCacheInvalidatedBySetInstance(): void
    {
        $injector = new Injector(new TopLevel());
        $this->assertFalse($injector->has(AnInterface::class));
        $injector->setInstance(AnInterface::class, new ClassImplementingAnInterface());
        $this->assertTrue($injector->has(AnInterface::class));
    }

    // --- autowire: no constructor (line 584-586) ---

    public function testHasTrueForConcreteNoConstructor(): void
    {
        $injector = new Injector(new TopLevel());
        $this->assertTrue($injector->has(stdClass::class));
    }

    // --- autowire: empty constructor (line 590-592) ---

    public function testHasTrueForConcreteEmptyConstructor(): void
    {
        $injector = new Injector(new TopLevel());
        $this->assertTrue($injector->has(HasEmptyConstructor::class));
    }

    // --- autowire: all resolvable deps (line 610) ---

    public function testHasTrueForAutowireableClass(): void
    {
        $injector = new Injector(new TopLevel());
        // ClassImplementingAnInterface has no constructor — autowireable
        // ClassDependingOnAutowireable depends on it
        $this->assertTrue($injector->has(HasAutowireableDep::class));
    }

    // --- autowire: all optional params (line 596-597) ---

    public function testHasTrueForAllOptionalParams(): void
    {
        $injector = new Injector(new TopLevel());
        $this->assertTrue($injector->has(ClassWithMultipleOptionalParams::class));
    }

    // --- abstract class (line 578-580) ---

    public function testHasFalseForAbstractClass(): void
    {
        $injector = new Injector(new TopLevel());
        $this->assertFalse($injector->has(HasAbstractFixture::class));
    }

    // --- non-existent class (line 572-574) ---

    public function testHasFalseForNonExistentClass(): void
    {
        $injector = new Injector(new TopLevel());
        $this->assertFalse($injector->has('Totally\Nonexistent\ClassName'));
    }

    // --- self-referencing constructor guard (line 600-603) ---

    public function testHasFalseForSelfReferencingConstructor(): void
    {
        $injector = new Injector(new TopLevel());
        $this->assertFalse($injector->has(HasSelfReference::class));
    }

    // --- union type string-cast bug (line 600, 605) ---

    public function testHasFalseForUnionTypeParam(): void
    {
        $injector = new Injector(new TopLevel());
        // Both union members are concrete and autowireable, so get() succeeds.
        // But has() casts the union ReflectionUnionType to string "A|B" and
        // calls has("A|B") which fails.
        // Documents PSR-11 violation: has()=false, get()=success.
        $this->assertFalse($injector->has(ClassDependingOnUnionType::class));
    }
}

class HasEmptyConstructor
{
    public function __construct()
    {
    }
}

class HasAutowireableDep
{
    public function __construct(
        public readonly ClassImplementingAnInterface $dep,
    ) {}
}

abstract class HasAbstractFixture
{
}

class HasSelfReference
{
    public function __construct(
        public readonly HasSelfReference $self,
    ) {}
}

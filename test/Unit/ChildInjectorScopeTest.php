<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit;

use Horde\Injector\Binder\Closure as ClosureBinder;
use Horde\Injector\Binder\Implementation;
use Horde\Injector\Injector;
use Horde\Injector\Test\Unit\Fixture\AnInterface;
use Horde\Injector\Test\Unit\Fixture\ClassImplementingAnInterface;
use Horde\Injector\TopLevel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(Injector::class)]
class ChildInjectorScopeTest extends TestCase
{
    /**
     * A binding added to a child injector must not be visible in the parent.
     */
    public function testChildBindingDoesNotLeakToParent(): void
    {
        $parent = new Injector(new TopLevel());
        $child = $parent->createChildInjector();

        $child->addBinder(
            AnInterface::class,
            new Implementation(ClassImplementingAnInterface::class)
        );

        // Child can resolve it
        $this->assertTrue($child->has(AnInterface::class));
        // Parent cannot
        $this->assertFalse($parent->has(AnInterface::class));
    }

    /**
     * setInstance() in a child does not affect the parent's instance cache.
     */
    public function testChildSetInstanceDoesNotAffectParent(): void
    {
        $parent = new Injector(new TopLevel());
        $child = $parent->createChildInjector();

        $child->setInstance('child.only', new stdClass());

        $this->assertTrue($child->hasInstance('child.only'));
        $this->assertFalse($parent->hasInstance('child.only'));
    }

    /**
     * An instance set in the parent is available to the child via get().
     */
    public function testParentInstanceAvailableToChild(): void
    {
        $parent = new Injector(new TopLevel());
        $obj = new stdClass();
        $parent->setInstance('shared.key', $obj);

        $child = $parent->createChildInjector();
        $this->assertSame($obj, $child->get('shared.key'));
    }

    /**
     * Child and parent can have different bindings for the same interface.
     * Each uses its own.
     */
    public function testChildOverridesParentBinding(): void
    {
        $parent = new Injector(new TopLevel());
        $child = $parent->createChildInjector();

        $parentObj = new stdClass();
        $parentObj->source = 'parent';
        $childObj = new stdClass();
        $childObj->source = 'child';

        $parent->setInstance('config', $parentObj);
        $child->setInstance('config', $childObj);

        $this->assertSame('parent', $parent->get('config')->source);
        $this->assertSame('child', $child->get('config')->source);
    }

    /**
     * Three-level nesting: parent → child → grandchild.
     * Parent instance visible in grandchild; grandchild binding not in parent.
     */
    public function testMultiLevelNesting(): void
    {
        $parent = new Injector(new TopLevel());
        $obj = new stdClass();
        $parent->setInstance('root.value', $obj);

        $child = $parent->createChildInjector();
        $grandchild = $child->createChildInjector();

        // Grandchild can reach the root instance
        $this->assertSame($obj, $grandchild->get('root.value'));

        // Grandchild binding not visible to parent or child
        $grandchild->setInstance('deep.value', new stdClass());
        $this->assertFalse($parent->hasInstance('deep.value'));
        $this->assertFalse($child->hasInstance('deep.value'));
    }

    /**
     * A child injector returns itself (not the parent) when Injector is requested.
     */
    public function testChildInjectorReturnsSelfNotParent(): void
    {
        $parent = new Injector(new TopLevel());
        $child = $parent->createChildInjector();

        $this->assertSame($child, $child->get(Injector::class));
        $this->assertSame($parent, $parent->get(Injector::class));
        $this->assertNotSame($parent, $child->get(Injector::class));
    }
}

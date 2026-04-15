<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit\Attribute;

use Horde\Injector\Attribute\Factory;
use Horde\Injector\Injector;
use Horde\Injector\Test\Unit\Fixture\AbstractClassWithFactory;
use Horde\Injector\Test\Unit\Fixture\ConcreteSubclass;
use Horde\Injector\Test\Unit\Fixture\InterfaceWithFactory;
use Horde\Injector\Test\Unit\Fixture\InterfaceWithFactoryImpl;
use Horde\Injector\TopLevel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for #[Factory] attribute discovery on abstract classes and interfaces.
 *
 * Documents two gaps in the Injector:
 * - Gap 1: has() does not check #[Factory] attributes (breaks PSR-11 contract)
 * - Gap 2: getBinder() uses class_exists() which excludes interfaces
 */
#[CoversClass(Factory::class)]
#[CoversClass(Injector::class)]
class FactoryAttributeGapTest extends TestCase
{
    /**
     * Gap 1: has() should return true for abstract class with #[Factory]
     */
    public function testHasReturnsTrueForAbstractClassWithFactoryAttribute(): void
    {
        $injector = new Injector(new TopLevel());
        $this->assertTrue($injector->has(AbstractClassWithFactory::class));
    }

    /**
     * Gap 1: get() should work for abstract class with #[Factory]
     */
    public function testGetWorksForAbstractClassWithFactoryAttribute(): void
    {
        $injector = new Injector(new TopLevel());
        $result = $injector->get(AbstractClassWithFactory::class);
        $this->assertInstanceOf(ConcreteSubclass::class, $result);
        $this->assertEquals('factory-created', $result->getSource());
    }

    /**
     * Gap 2: has() should return true for interface with #[Factory]
     */
    public function testHasReturnsTrueForInterfaceWithFactoryAttribute(): void
    {
        $injector = new Injector(new TopLevel());
        $this->assertTrue($injector->has(InterfaceWithFactory::class));
    }

    /**
     * Gap 2: get() should work for interface with #[Factory]
     */
    public function testGetWorksForInterfaceWithFactoryAttribute(): void
    {
        $injector = new Injector(new TopLevel());
        $result = $injector->get(InterfaceWithFactory::class);
        $this->assertInstanceOf(InterfaceWithFactoryImpl::class, $result);
        $this->assertEquals('factory-created', $result->getSource());
    }

    /**
     * PSR-11 consistency: has() and get() must agree for abstract with #[Factory]
     */
    public function testHasAndGetConsistentForAbstractWithFactory(): void
    {
        $injector = new Injector(new TopLevel());
        $has = $injector->has(AbstractClassWithFactory::class);
        $this->assertTrue($has, 'has() must return true when get() can succeed');
        $result = $injector->get(AbstractClassWithFactory::class);
        $this->assertInstanceOf(AbstractClassWithFactory::class, $result);
    }

    /**
     * PSR-11 consistency: has() and get() must agree for interface with #[Factory]
     */
    public function testHasAndGetConsistentForInterfaceWithFactory(): void
    {
        $injector = new Injector(new TopLevel());
        $has = $injector->has(InterfaceWithFactory::class);
        $this->assertTrue($has, 'has() must return true when get() can succeed');
        $result = $injector->get(InterfaceWithFactory::class);
        $this->assertInstanceOf(InterfaceWithFactory::class, $result);
    }

    /**
     * Caching: after get() resolves via attribute, has() must also return true
     */
    public function testHasReturnsTrueAfterGetForAbstractWithFactory(): void
    {
        $injector = new Injector(new TopLevel());
        $injector->get(AbstractClassWithFactory::class);
        $this->assertTrue($injector->has(AbstractClassWithFactory::class));
    }

    /**
     * Explicit binding still overrides #[Factory] on abstract class
     */
    public function testExplicitBindingOverridesAttributeOnAbstractClass(): void
    {
        $injector = new Injector(new TopLevel());
        $custom = new ConcreteSubclass();
        $injector->setInstance(AbstractClassWithFactory::class, $custom);
        $result = $injector->get(AbstractClassWithFactory::class);
        $this->assertSame($custom, $result);
    }
}

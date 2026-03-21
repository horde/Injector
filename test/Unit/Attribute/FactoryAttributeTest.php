<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit\Attribute;

use Horde\Injector\Attribute\Factory;
use Horde\Injector\Injector;
use Horde\Injector\TopLevel;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class FactoryAttributeTest extends TestCase
{
    public function testFactoryAttributeAutoDiscovery(): void
    {
        $injector = new Injector(new TopLevel());

        // Get instance of class with Factory attribute
        $widget = $injector->get(AttributeTestWidget::class);

        $this->assertInstanceOf(AttributeTestWidget::class, $widget);
        $this->assertEquals('factory-created', $widget->getSource());
    }

    public function testExplicitBindingOverridesAttribute(): void
    {
        $injector = new Injector(new TopLevel());

        // Explicit binding should take precedence over attribute
        $injector->bindClosure(
            AttributeTestWidget::class,
            fn($inj) => new AttributeTestWidget('explicit-binding')
        );

        $widget = $injector->get(AttributeTestWidget::class);
        $this->assertEquals('explicit-binding', $widget->getSource());
    }

    public function testFactoryAttributeWithoutRequiredParameters(): void
    {
        $injector = new Injector(new TopLevel());

        // Class with incomplete Factory attribute should fallback to autowiring
        $obj = $injector->get(AttributeTestInvalidFactory::class);
        $this->assertInstanceOf(AttributeTestInvalidFactory::class, $obj);
    }

    public function testFactoryAttributeCachedAfterFirstAccess(): void
    {
        $injector = new Injector(new TopLevel());

        $widget1 = $injector->get(AttributeTestWidget::class);
        $widget2 = $injector->get(AttributeTestWidget::class);

        // Should return same instance (cached)
        $this->assertSame($widget1, $widget2);
    }
}

/**
 * Test fixture: Factory class
 */
class AttributeTestWidgetFactory
{
    public function createWidget(Injector $injector): AttributeTestWidget
    {
        return new AttributeTestWidget('factory-created');
    }
}

/**
 * Test fixture: Target class with Factory attribute
 */
#[Factory(factory: AttributeTestWidgetFactory::class, method: 'createWidget')]
class AttributeTestWidget
{
    public function __construct(
        private readonly string $source
    ) {}

    public function getSource(): string
    {
        return $this->source;
    }
}

/**
 * Test fixture: Class with incomplete Factory attribute (no method)
 */
#[Factory(factory: AttributeTestWidgetFactory::class)]
class AttributeTestInvalidFactory
{
    public function __construct() {}
}

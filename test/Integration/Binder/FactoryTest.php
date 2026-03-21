<?php
declare(strict_types=1);

namespace Horde\Injector\Test\Integration\Binder;

use Horde\Injector\Binder\Factory;
use Horde\Injector\Injector;
use Horde\Injector\TopLevel;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    public function testShouldCallFactoryMethod(): void
    {
        $factory = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $factory->expects($this->once())
            ->method('create')
            ->with()
            ->willReturn('INSTANCE');
        $factoryClassName = get_class($factory);

        $childInjector = $this->getMockBuilder(Injector::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createInstance', 'getInstance'])
            ->getMock();
        $childInjector->expects($this->once())
            ->method('getInstance')
            ->with($this->equalTo($factoryClassName))
            ->willReturn($factory);

        $injector = $this->getMockBuilder(Injector::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createChildInjector'])
            ->getMock();
        $injector->expects($this->once())
            ->method('createChildInjector')
            ->with()
            ->willReturn($childInjector);

        $factoryBinder = new Factory($factoryClassName, 'create');

        $this->assertEquals('INSTANCE', $factoryBinder->create($injector));
    }

    /**
     * The factory binder should pass a child injector object to the factory, so that
     * any configuration that happens in the factory will not bleed into global scope
     */
    public function testShouldPassChildInjectorToFactoryMethod(): void
    {
        $factory = new FactoryTestMockFactory();

        $binder = new Factory(FactoryTestMockFactory::class, 'create');

        $injector = new FactoryTestInjectorMock(new TopLevel());
        $injector->TEST_ID = "PARENTINJECTOR";

        // set the instance so we know we'll get our factory object from the injector
        $injector->setInstance(FactoryTestMockFactory::class, $factory);

        // calling create should pass a child injector to the factory
        $binder->create($injector);

        // now the factory should have a reference to a child injector
        $this->assertEquals(
            $injector->TEST_ID . "->CHILD",
            $factory->getInjector()->TEST_ID,
            "Incorrect Injector passed to factory method"
        );
    }

    /**
     * This test guarantees that our mock factory stores the injector that was given to it,
     * so that we may inspect it later and prove what injector is actually given to it
     */
    public function testMockFactoryStoresPassedInjector(): void
    {
        $factory = new FactoryTestMockFactory();
        $injector = new FactoryTestInjectorMock(new TopLevel());
        $injector->TEST_ID = "INJECTOR";
        $factory->create($injector);

        $this->assertEquals($injector, $factory->getInjector());
    }

    public function testShouldReturnBindingDetails(): void
    {
        $factoryBinder = new Factory('FACTORY', 'METHOD');

        $this->assertEquals('FACTORY', $factoryBinder->getFactory());
        $this->assertEquals('METHOD', $factoryBinder->getMethod());
    }
}

/**
 * Test fixture classes
 */

class FactoryTestMockFactory
{
    private ?Injector $injector = null;

    public function getInjector(): ?Injector
    {
        return $this->injector;
    }

    public function create(Injector $injector): void
    {
        $this->injector = $injector;
    }
}

class FactoryTestInjectorMock extends Injector
{
    public string $TEST_ID = '';

    public function createChildInjector(): Injector
    {
        $child = new self($this);
        $child->TEST_ID = $this->TEST_ID . "->CHILD";
        return $child;
    }
}

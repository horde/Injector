<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit;

use Closure;
use Horde\Injector\Binder\Factory;
use Horde\Injector\Binder\Implementation;
use Horde\Injector\Injector;
use Horde\Injector\TopLevel;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Injector::class)]
class LoadBindingsTest extends TestCase
{
    public function testLoadImplementationBinding(): void
    {
        $injector = new Injector(new TopLevel());
        $injector->loadBindings([
            LoadBindingsTestInterface::class => LoadBindingsTestConcrete::class,
        ]);
        $result = $injector->get(LoadBindingsTestInterface::class);
        $this->assertInstanceOf(LoadBindingsTestConcrete::class, $result);
    }

    public function testLoadFactoryBinding(): void
    {
        $injector = new Injector(new TopLevel());
        $injector->loadBindings([
            LoadBindingsTestInterface::class => [LoadBindingsTestFactory::class, 'create'],
        ]);
        $result = $injector->get(LoadBindingsTestInterface::class);
        $this->assertInstanceOf(LoadBindingsTestConcrete::class, $result);
    }

    public function testLoadClosureBinding(): void
    {
        $injector = new Injector(new TopLevel());
        $concrete = new LoadBindingsTestConcrete();
        $injector->loadBindings([
            LoadBindingsTestInterface::class => fn(Injector $i) => $concrete,
        ]);
        $result = $injector->get(LoadBindingsTestInterface::class);
        $this->assertSame($concrete, $result);
    }

    public function testLoadMixedBindings(): void
    {
        $injector = new Injector(new TopLevel());
        $concrete = new LoadBindingsTestConcrete();
        $injector->loadBindings([
            LoadBindingsTestInterface::class => LoadBindingsTestConcrete::class,
            'factory.key' => [LoadBindingsTestFactory::class, 'create'],
            'closure.key' => fn(Injector $i) => $concrete,
        ]);
        $this->assertInstanceOf(LoadBindingsTestConcrete::class, $injector->get(LoadBindingsTestInterface::class));
        $this->assertInstanceOf(LoadBindingsTestConcrete::class, $injector->get('factory.key'));
        $this->assertSame($concrete, $injector->get('closure.key'));
    }

    public function testLoadBindingsViaConstructor(): void
    {
        $injector = new Injector(new TopLevel(), [
            LoadBindingsTestInterface::class => LoadBindingsTestConcrete::class,
        ]);
        $result = $injector->get(LoadBindingsTestInterface::class);
        $this->assertInstanceOf(LoadBindingsTestConcrete::class, $result);
    }

    public function testLoadBindingsIsAdditive(): void
    {
        $injector = new Injector(new TopLevel());
        $injector->loadBindings([
            'key.a' => LoadBindingsTestConcrete::class,
        ]);
        $injector->loadBindings([
            'key.b' => LoadBindingsTestConcrete::class,
        ]);
        $this->assertInstanceOf(LoadBindingsTestConcrete::class, $injector->get('key.a'));
        $this->assertInstanceOf(LoadBindingsTestConcrete::class, $injector->get('key.b'));
    }

    public function testLoadBindingsCoexistsWithImperativeApi(): void
    {
        $injector = new Injector(new TopLevel());
        $injector->loadBindings([
            'declarative.key' => LoadBindingsTestConcrete::class,
        ]);
        $injector->bindImplementation('imperative.key', LoadBindingsTestConcrete::class);
        $this->assertInstanceOf(LoadBindingsTestConcrete::class, $injector->get('declarative.key'));
        $this->assertInstanceOf(LoadBindingsTestConcrete::class, $injector->get('imperative.key'));
    }

    public function testInvalidBindingValueThrows(): void
    {
        $injector = new Injector(new TopLevel());
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported binding type');
        $injector->loadBindings([
            'bad.key' => 42,
        ]);
    }

    public function testMalformedFactoryArrayThrows(): void
    {
        $injector = new Injector(new TopLevel());
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Factory binding');
        $injector->loadBindings([
            'bad.key' => ['only_one_element'],
        ]);
    }

    public function testEmptyBindingsArrayIsNoop(): void
    {
        $injector = new Injector(new TopLevel());
        $injector->loadBindings([]);
        // No exception, injector still works
        $this->assertTrue($injector->has(Injector::class));
    }

    public function testLoadBindingsReturnsSelf(): void
    {
        $injector = new Injector(new TopLevel());
        $result = $injector->loadBindings([]);
        $this->assertSame($injector, $result);
    }

    public function testImperativeOverridesDeclarative(): void
    {
        $injector = new Injector(new TopLevel());
        $first = new LoadBindingsTestConcrete();
        $second = new LoadBindingsTestConcrete();
        $injector->loadBindings([
            LoadBindingsTestInterface::class => fn(Injector $i) => $first,
        ]);
        // Imperative call after loadBindings overrides
        $injector->bindClosure(LoadBindingsTestInterface::class, fn(Injector $i) => $second);
        $this->assertSame($second, $injector->get(LoadBindingsTestInterface::class));
    }

    public function testDeclarativeOverridesImperative(): void
    {
        $injector = new Injector(new TopLevel());
        $first = new LoadBindingsTestConcrete();
        $second = new LoadBindingsTestConcrete();
        $injector->bindClosure(LoadBindingsTestInterface::class, fn(Injector $i) => $first);
        // loadBindings after imperative call overrides
        $injector->loadBindings([
            LoadBindingsTestInterface::class => fn(Injector $i) => $second,
        ]);
        $this->assertSame($second, $injector->get(LoadBindingsTestInterface::class));
    }
}

interface LoadBindingsTestInterface {}

class LoadBindingsTestConcrete implements LoadBindingsTestInterface {}

class LoadBindingsTestFactory
{
    public function create(Injector $injector): LoadBindingsTestConcrete
    {
        return new LoadBindingsTestConcrete();
    }
}

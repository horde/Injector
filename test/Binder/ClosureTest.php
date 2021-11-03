<?php
namespace Horde\Injector\Test\Binder;
use Horde\Injector\Binder;
use Horde\Injector\Binder\Closure;
use Horde\Injector\DependencyFinder;
use Horde\Injector\Injector;
use Horde\Injector\TopLevel;

class ClosureTest extends \Horde\Test\TestCase
{
    public function testShouldCallClosure()
    {
        $childInjector = $this->getMockSkipConstructor('Horde\Injector\Injector', ['createInstance', 'getInstance']);
        $injector = $this->getMockSkipConstructor('Horde\Injector\Injector', ['createChildInjector']);
        $injector->expects($this->once())
            ->method('createChildInjector')
            ->with()
            ->will($this->returnValue($childInjector));

        $closureBinder = new Closure(
            function (Injector $injector) { return 'INSTANCE'; }
        );

        $this->assertEquals('INSTANCE', $closureBinder->create($injector));
    }

    /**
     * The closure binder should pass a child injector object to the closure, so that
     * any configuration that happens in the closure will not bleed into global scope
     */
    public function testShouldPassChildInjectorToClosure()
    {
        $closure = function (Injector $injector) { return $injector; };

        $binder = new Closure($closure);

        $injector = new ClosureInjectorMockTestAccess(new TopLevel());
        $injector->TEST_ID = "PARENTINJECTOR";

        // calling create should pass a child injector to the factory
        $childInjector = $binder->create($injector);

        // now the factory should have a reference to a child injector
        $this->assertEquals($injector->TEST_ID . "->CHILD", $childInjector->TEST_ID, "Incorrect Injector passed to closure");
    }

    public function testShouldReturnBindingDetails()
    {
        $closure = function (Injector $injector) {};
        $closureBinder = new Closure(
            $closure
        );

        $this->assertEquals($closure, $closureBinder->getClosure());
    }
}

class ClosureInjectorMockTestAccess extends Injector
{
    public function createChildInjector(): Injector
    {
        $child = new self($this);
        $child->TEST_ID = $this->TEST_ID . "->CHILD";
        return $child;
    }
}

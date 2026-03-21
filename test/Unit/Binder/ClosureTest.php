<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit\Binder;

use Horde\Injector\Binder\Closure;
use Horde\Injector\Injector;
use Horde\Injector\TopLevel;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class ClosureTest extends TestCase
{
    public function testShouldCallClosure(): void
    {
        $childInjector = $this->getMockBuilder(Injector::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createInstance', 'getInstance'])
            ->getMock();

        $injector = $this->getMockBuilder(Injector::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createChildInjector'])
            ->getMock();

        $injector->expects($this->once())
            ->method('createChildInjector')
            ->with()
            ->willReturn($childInjector);

        $closureBinder = new Closure(
            function (Injector $injector) {
                return 'INSTANCE';
            }
        );

        $this->assertEquals('INSTANCE', $closureBinder->create($injector));
    }

    /**
     * The closure binder should pass a child injector object to the closure, so that
     * any configuration that happens in the closure will not bleed into global scope
     */
    public function testShouldPassChildInjectorToClosure(): void
    {
        $closure = function (Injector $injector) {
            return $injector;
        };

        $binder = new Closure($closure);

        $injector = new ClosureTestInjectorMock(new TopLevel());
        $injector->TEST_ID = "PARENTINJECTOR";

        // calling create should pass a child injector to the factory
        $childInjector = $binder->create($injector);

        // now the factory should have a reference to a child injector
        $this->assertEquals(
            $injector->TEST_ID . "->CHILD",
            $childInjector->TEST_ID,
            "Incorrect Injector passed to closure"
        );
    }

    public function testShouldReturnBindingDetails(): void
    {
        $closure = function (Injector $injector) {};
        $closureBinder = new Closure($closure);

        $this->assertEquals($closure, $closureBinder->getClosure());
    }
}

/**
 * Mock injector for closure tests that adds TEST_ID to child injectors
 */
class ClosureTestInjectorMock extends Injector
{
    public string $TEST_ID = '';

    public function createChildInjector(): Injector
    {
        $child = new self($this);
        $child->TEST_ID = $this->TEST_ID . "->CHILD";
        return $child;
    }
}

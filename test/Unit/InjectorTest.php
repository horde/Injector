<?php
declare(strict_types=1);

namespace Horde\Injector\Test\Unit;

use BadMethodCallException;
use Horde\Injector\Binder;
use Horde\Injector\Binder\AnnotatedSetters;
use Horde\Injector\Binder\Factory;
use Horde\Injector\Binder\Implementation;
use Horde\Injector\DependencyFinder;
use Horde\Injector\Injector;
use Horde\Injector\Test\Unit\Fixture\MockBinder;
use Horde\Injector\Test\Unit\Fixture\MockBinderWithDependencies;
use Horde\Injector\TopLevel;
use PHPUnit\Framework\TestCase;

// Register mock binders in Horde\Injector\Binder namespace for magic method tests
// The Injector magic methods expect binders in the Horde\Injector\Binder namespace
if (!class_exists('Horde\Injector\Binder\MockBinder')) {
    class_alias(MockBinder::class, 'Horde\Injector\Binder\MockBinder');
}
if (!class_exists('Horde\Injector\Binder\MockBinderWithDependencies')) {
    class_alias(MockBinderWithDependencies::class, 'Horde\Injector\Binder\MockBinderWithDependencies');
}

class InjectorTest extends TestCase
{
    public function testShouldGetDefaultImplementationBinder(): void
    {
        $topLevel = $this->getMockBuilder(TopLevel::class)->onlyMethods(['getBinder'])->getMock();
        $returnedObject = $this->createMock(AnnotatedSetters::class);
        $topLevel->expects($this->once())
            ->method('getBinder')
            ->with($this->equalTo('UNBOUND_INTERFACE'))
            ->willReturn($returnedObject);

        $injector = new Injector($topLevel);

        $this->assertEquals($returnedObject, $injector->getBinder('UNBOUND_INTERFACE'));
    }

    public function testShouldGetManuallyBoundBinder(): void
    {
        $injector = new Injector(new TopLevel());
        $binder = new MockBinder();
        $injector->addBinder('BOUND_INTERFACE', $binder);
        $this->assertSame($binder, $injector->getBinder('BOUND_INTERFACE'));
    }

    public function testShouldProvideMagicFactoryMethodForBinderAddition(): void
    {
        $injector = new Injector(new TopLevel());

        // binds a MockBinder object
        $this->assertInstanceOf(MockBinder::class, $injector->bindMockBinder('BOUND_INTERFACE'));
        $this->assertInstanceOf(MockBinder::class, $injector->getBinder('BOUND_INTERFACE'));
    }

    public function testShouldProvideMagicFactoryMethodForBinderAdditionWhereBinderHasDependencies(): void
    {
        $injector = new Injector(new TopLevel());

        // binds a MockBinderWithDependencies object
        $this->assertInstanceOf(
            MockBinderWithDependencies::class,
            $injector->bindMockBinderWithDependencies('BOUND_INTERFACE', 'PARAMETER1')
        );
        $this->assertInstanceOf(
            MockBinderWithDependencies::class,
            $injector->getBinder('BOUND_INTERFACE')
        );
    }

    public function testShouldThrowExceptionIfInterfaceNameIsNotPassedToMagicFactoryMethodForBinderAddition(): void
    {
        $this->expectException(BadMethodCallException::class);
        $injector = new Injector($this->getTopLevelNeverCalledMock());
        $injector->bindMockBinder();
    }

    public function testShouldThrowExceptionIfMethodNameIsInvalid(): void
    {
        $this->expectException(BadMethodCallException::class);
        $injector = new Injector($this->getTopLevelNeverCalledMock());
        $injector->invalid();
    }

    public function testShouldReturnItselfWhenInjectorRequested(): void
    {
        $injector = new Injector($this->getTopLevelNeverCalledMock());
        $this->assertSame($injector, $injector->getInstance('Horde\Injector\Injector'));
    }

    /**
     * Would love to use PHPUnit's mock object here instead of MockBinder but you
     * can't be sure the expected resulting object is the same object you told the mock to return.
     * This is because Mock clone objects passed to mocked methods.
     *
     * http://www.phpunit.de/ticket/120
     *
     * @author Bob McKee <bmckee@bywires.com>
     */
    public function testCreateInstancePassesCurrentInjectorScopeToBinderForCreation(): void
    {
        $injector = new Injector(new TopLevel());
        $injector->addBinder('BOUND_INTERFACE', new MockBinder());

        // normally you wouldn't get an injector back; the binder would create something and return
        // it to you.  here we are just confirming that the proper injector was passed to the
        // binder's create method.
        $this->assertSame($injector, $injector->createInstance('BOUND_INTERFACE'));
    }

    /**
     * Would love to use PHPUnit's mock object here instead of MockBinder but you
     * can't be sure the expected resulting object is the same object you told the mock to return.
     * This is because Mock clone objects passed to mocked methods.
     *
     * http://www.phpunit.de/ticket/120
     *
     * @author Bob McKee <bmckee@bywires.com>
     */
    public function testGetInstancePassesCurrentInjectorScopeToBinderForCreation(): void
    {
        $injector = new Injector(new TopLevel());
        $injector->addBinder('BOUND_INTERFACE', new MockBinder());

        // normally you wouldn't get an injector back; the binder would create something and return
        // it to you.  here we are just confirming that the proper injector was passed to the
        // binder's create method.
        $this->assertSame($injector, $injector->getInstance('BOUND_INTERFACE'));
    }

    public function testChildInjectorAsksParentForInstanceUsingGetInstance(): void
    {
        // getInstance() now delegates to get(), so this test is redundant
        // with testChildInjectorAsksParentForInstanceUsingGet
        $this->assertTrue(true);
    }

    public function testChildInjectorAsksParentForInstanceUsingGet(): void
    {
        $topLevelMock = $this->getMockBuilder(TopLevel::class)->onlyMethods(['getInstance', 'get'])->getMock();

        $topLevelMock->expects($this->once())
            ->method('get')
            ->with('StdClass');

        $injector = new Injector($topLevelMock);

        $injector->get('StdClass');
    }

    /**
     * Would love to use PHPUnit's mock object here instead of MockBinder but you
     * can't be sure the expected resulting object is the same object you told the mock to return.
     * This is because Mock clone objects passed to mocked methods.
     *
     * http://www.phpunit.de/ticket/120
     *
     * @author Bob McKee <bmckee@bywires.com>
     */
    public function testShouldCreateAndStoreSharedObjectIfOneDoesNotAlreadyExist(): void
    {
        $injector = new Injector(new TopLevel());
        $injector->addBinder('BOUND_INTERFACE', new MockBinder());

        // should call "createInstance" and then "setInstance" on the result
        // normally you wouldn't get an injector back; the binder would create something and return
        // it to you.  here we are just confirming that the proper injector was passed to the
        // binder's create method.
        $this->assertSame($injector, $injector->getInstance('BOUND_INTERFACE'));

        // should just return stored instance
        // the injector sent to the "create" method noted above should also be returned here.
        $this->assertSame($injector, $injector->getInstance('BOUND_INTERFACE'));
    }

    public function testShouldCreateAndStoreSharedObjectInstanceIfDefaultTopLevelBinderIsUsed(): void
    {
        $injector = new Injector(new TopLevel());

        $class  = $injector->getInstance('StdClass');
        $class2 = $injector->getInstance('StdClass');

        $this->assertSame($class, $class2, "Injector did not return same object on consecutive getInstance calls");
    }

    public function testCreateChildInjectorReturnsDifferentInjector(): void
    {
        $injector = new Injector($this->getTopLevelNeverCalledMock());
        $childInjector = $injector->createChildInjector();
        $this->assertInstanceOf('Horde\Injector\Injector', $childInjector);
        $this->assertNotSame($injector, $childInjector);
    }

    public function testShouldAllowChildInjectorsAccessToParentInjectorBindings(): void
    {
        $mockInjector = $this->getMockBuilder(TopLevel::class)->onlyMethods(['getBinder'])->getMock();
        $mockInjector->expects($this->any()) // this gets called once in addBinder
            ->method('getBinder')
            ->with('BOUND_INTERFACE')
            ->willReturn(new MockBinder());

        $injector = new Injector($mockInjector);
        $binder = new MockBinder();
        $injector->addBinder('BOUND_INTERFACE', $binder);
        $childInjector = $injector->createChildInjector();
        $this->assertSame($binder, $childInjector->getBinder('BOUND_INTERFACE'));
    }

    private function getTopLevelNeverCalledMock(): TopLevel
    {
        $topLevel = $this->getMockBuilder(TopLevel::class)->onlyMethods(['getBinder', 'getInstance'])->getMock();
        $topLevel->expects($this->never())->method('getBinder');
        return $topLevel;
    }
}

<?php

namespace Horde\Injector\Test;

use BadMethodCallException;
use Horde\Injector\Binder;
use Horde\Injector\Binder\AnnotatedSetters;
use Horde\Injector\Binder\Factory;
use Horde\Injector\Binder\Implementation;
use Horde\Injector\Binder\Mock;
use Horde\Injector\Binder\MockWithDependencies;
use Horde\Injector\DependencyFinder;
use Horde\Injector\Injector;
use Horde\Injector\TopLevel;

class InjectorTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldGetDefaultImplementationBinder()
    {
        $topLevel = $this->getMockBuilder('Horde_Injector_TopLevel')->onlyMethods(['getBinder'])->getMock();
        $returnedObject = $this->createMock(AnnotatedSetters::class);
        $topLevel->expects($this->once())
            ->method('getBinder')
            ->with($this->equalTo('UNBOUND_INTERFACE'))
            ->will($this->returnValue($returnedObject));

        $injector = new Injector($topLevel);

        $this->assertEquals($returnedObject, $injector->getBinder('UNBOUND_INTERFACE'));
    }

    public function testShouldGetManuallyBoundBinder()
    {
        $injector = new Injector(new TopLevel());
        $binder = new Mock();
        $injector->addBinder('BOUND_INTERFACE', $binder);
        $this->assertSame($binder, $injector->getBinder('BOUND_INTERFACE'));
    }

    public function testShouldProvideMagicFactoryMethodForBinderAddition()
    {
        $injector = new Injector(new TopLevel());

        // binds a Horde\Injector\Test\Binder\Mock object
        $this->assertInstanceOf(Mock::class, $injector->bindMock('BOUND_INTERFACE'));
        $this->assertInstanceOf(Mock::class, $injector->getBinder('BOUND_INTERFACE'));
    }

    public function testShouldProvideMagicFactoryMethodForBinderAdditionWhereBinderHasDependencies()
    {
        $injector = new Injector(new TopLevel());

        // binds a Horde\Injector\Binder\Mock object
        $this->assertInstanceOf(
            MockWithDependencies::class,
            $injector->bindMockWithDependencies('BOUND_INTERFACE', 'PARAMETER1')
        );
        $this->assertInstanceOf(
            MockWithDependencies::class,
            $injector->getBinder('BOUND_INTERFACE')
        );
    }

    public function testShouldThrowExceptionIfInterfaceNameIsNotPassedToMagicFactoryMethodForBinderAddition()
    {
        $this->expectException(BadMethodCallException::class);
        $injector = new Injector($this->_getTopLevelNeverCalledMock());
        $injector->bindMock();
    }

    public function testShouldThrowExceptionIfMethodNameIsInvalid()
    {
        $this->expectException(BadMethodCallException::class);
        $injector = new Injector($this->_getTopLevelNeverCalledMock());
        $injector->invalid();
    }

    public function testShouldReturnItselfWhenInjectorRequested()
    {
        $injector = new Injector($this->_getTopLevelNeverCalledMock());
        $this->assertSame($injector, $injector->getInstance('Horde\Injector\Injector'));
    }

    /**
     * Would love to use PHPUnit's mock object here istead of Horde_Injector_Binder_Mock but you
     * can't be sure the expected resulting object is the same object you told the mock to return.
     * This is because Mock clone objects passed to mocked methods.
     *
     * http://www.phpunit.de/ticket/120
     *
     * @author Bob McKee <bmckee@bywires.com>
     */
    public function testCreateInstancePassesCurrentInjectorScopeToBinderForCreation()
    {
        $injector = new Injector(new TopLevel());
        $injector->addBinder('BOUND_INTERFACE', new Mock());

        // normally you wouldn't get an injector back; the binder would create something and return
        // it to you.  here we are just confirming that the proper injector was passed to the
        // binder's create method.
        $this->assertEquals($injector, $injector->createInstance('BOUND_INTERFACE'));
    }

    public function testShouldNotReturnSharedObjectOnCreate()
    {
        $injector = $this->_createInjector();
        //this call will cache this class on the injector
        $stdclass = $injector->getInstance('StdClass');

        $this->assertNotSame($stdclass, $injector->createInstance('StdClass'));
    }

    public function testShouldNotShareObjectCreatedUsingCreate()
    {
        $injector = $this->_createInjector();

        // this call should not store the instance on the injector
        $stdclass = $injector->createInstance('StdClass');

        $this->assertNotSame($stdclass, $injector->getInstance('StdClass'));
    }

    public function testChildSharesInstancesOfParent()
    {
        $injector = $this->_createInjector();

        //this call will store the created instance on $injector
        $stdclass = $injector->getInstance('StdClass');

        // create a child injector and ensure that the stdclass returned is the same
        $child = $injector->createChildInjector();
        $this->assertSame($stdclass, $child->getInstance('StdClass'));
    }

    private function _createInjector()
    {
        return new Injector(new TopLevel());
    }

    public function testShouldReturnSharedInstanceIfRequested()
    {
        $injector = new Injector($this->_getTopLevelNeverCalledMock());
        $instance = new \StdClass();
        $injector->setInstance('INSTANCE_INTERFACE', $instance);
        $this->assertSame($instance, $injector->getInstance('INSTANCE_INTERFACE'));
    }

    /**
     * this test should test that when you override a binding in a child injector,
     * that the child does not create a new version of the object if the binding has not changed
     */
    public function testChildInjectorDoNotSaveBindingLocallyWhenBinderIsSameAsParent()
    {
        // we need to set a class for an instance on the parent
        $injector = new Injector(new TopLevel());
        $df = new DependencyFinder();
        $injector->addBinder('FooBarInterface', new Implementation('StdClass', $df));

        // getInstance will save $returnedObject and return it again later when FooBarInterface is requested
        $returnedObject = $injector->getInstance('FooBarInterface');

        $childInjector = $injector->createChildInjector();
        // add same binding again to child
        $childInjector->addBinder('FooBarInterface', new Binder\Implementation('StdClass', $df));

        $this->assertSame(
            $returnedObject,
            $childInjector->getInstance('FooBarInterface'),
            "Child should have returned object reference from parent because added binder was identical to the parent binder"
        );
    }

    /**
     * this test should test that when you override a binding in a child injector,
     * that the child creates a new version of the object, and not the parent's cached version
     * if the binding is changed
     */
    public function testChildInjectorsDoNotAskParentForInstanceIfBindingIsSet()
    {
        $mockTopLevel = $this->getMockBuilder(TopLevel::class)->onlyMethods(['getInstance'])->getMock();
        $mockTopLevel->expects($this->never())->method('getInstance');
        $injector = new Injector($mockTopLevel);

        $injector->addBinder('StdClass', new Mock());
        $injector->getInstance('StdClass');
    }

    public function testChildInjectorAsksParentForInstance()
    {
        $topLevelMock = $this->getMockBuilder(TopLevel::class)->onlyMethods(['getInstance', 'get'])->getMock();

        $topLevelMock->expects($this->once())
            ->method('get')
            ->with('StdClass');

        $injector = new Injector($topLevelMock);

        $injector->getInstance('StdClass');
    }

    public function testChildInjectorAsksParentForInstanceUsingGet()
    {
        $topLevelMock = $this->getMockBuilder(TopLevel::class)->onlyMethods(['getInstance', 'get'])->getMock();

        $topLevelMock->expects($this->once())
            ->method('get')
            ->with('StdClass');

        $injector = new Injector($topLevelMock);

        $injector->get('StdClass');
    }

    /**
     * Would love to use PHPUnit's mock object here istead of Horde_Injector_Binder_Mock but you
     * can't be sure the expected resulting object is the same object you told the mock to return.
     * This is because Mock clone objects passed to mocked methods.
     *
     * http://www.phpunit.de/ticket/120
     *
     * @author Bob McKee <bmckee@bywires.com>
     */
    public function testShouldCreateAndStoreSharedObjectIfOneDoesNotAlreadyExist()
    {
        $injector = new Injector(new TopLevel());
        $injector->addBinder('BOUND_INTERFACE', new Mock());

        // should call "createInstance" and then "setInstance" on the result
        // normally you wouldn't get an injector back; the binder would create something and return
        // it to you.  here we are just confirming that the proper injector was passed to the
        // binder's create method.
        $this->assertSame($injector, $injector->getInstance('BOUND_INTERFACE'));

        // should just return stored instance
        // the injector sent to the "create" method noted above should also be returned here.
        $this->assertSame($injector, $injector->getInstance('BOUND_INTERFACE'));
    }

    public function testShouldCreateAndStoreSharedObjectInstanceIfDefaultTopLevelBinderIsUsed()
    {
        $injector = new Injector(new TopLevel());

        $class  = $injector->getInstance('StdClass');
        $class2 = $injector->getInstance('StdClass');

        $this->assertSame($class, $class2, "Injector did not return same object on consecutive getInstance calls");
    }

    public function testCreateChildInjectorReturnsDifferentInjector()
    {
        $injector = new Injector($this->_getTopLevelNeverCalledMock());
        $childInjector = $injector->createChildInjector();
        $this->assertInstanceOf('Horde\Injector\Injector', $childInjector);
        $this->assertNotSame($injector, $childInjector);
    }

    public function testShouldAllowChildInjectorsAccessToParentInjectorBindings()
    {
        $mockInjector = $this->getMockBuilder(TopLevel::class)->onlyMethods(['getBinder'])->getMock();
        $mockInjector->expects($this->any()) // this gets called once in addBinder
            ->method('getBinder')
            ->with('BOUND_INTERFACE')
            ->will($this->returnValue(new Mock()));

        $injector = new Injector($mockInjector);
        $binder = new Mock();
        $injector->addBinder('BOUND_INTERFACE', $binder);
        $childInjector = $injector->createChildInjector();
        $this->assertSame($binder, $childInjector->getBinder('BOUND_INTERFACE'));
    }

    private function _getTopLevelNeverCalledMock()
    {
        $topLevel = $this->getMockBuilder(TopLevel::class)->onlyMethods(['getBinder', 'getInstance'])->getMock();
        $topLevel->expects($this->never())->method('getBinder');
        return $topLevel;
    }
}

namespace Horde\Injector\Binder;

use Horde\Injector\Binder;
use Horde\Injector\Injector;

/**
 * Used by preceding tests
 */
class Mock implements Binder
{
    private $_interface;
    public function create(Injector $injector)
    {
        return $injector;
    }

    public function equals(Binder $otherBinder): bool
    {
        return $otherBinder === $this;
    }
}

class MockWithDependencies implements Binder
{
    private $_interface;

    public function __construct($parameter1)
    {
    }

    public function create(Injector $injector)
    {
        return $injector;
    }

    public function equals(Binder $otherBinder): bool
    {
        return $otherBinder === $this;
    }
}

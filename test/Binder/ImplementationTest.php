<?php
namespace Horde\Injector\Binder;
use Horde\Injector\Binder;
use Horde\Injector\DependencyFinder;
use Horde\Injector\Injector;
use Horde\Injector\NotFoundException;
use Horde\Injector\TopLevel;

class ImplementationTest extends \Horde_Test_Case
{
    public function setUp()
    {
        $this->df = new DependencyFinder();
    }

    public function testShouldReturnBindingDetails()
    {
        $implBinder = new Implementation(
            'IMPLEMENTATION',
            $this->df
        );

        $this->assertEquals('IMPLEMENTATION', $implBinder->getImplementation());
    }

    public function testShouldCreateInstanceOfClassWithNoDependencies()
    {
        $implBinder = new Implementation(
            'Horde\Injector\Binder\ImplementationTest__NoDependencies',
            $this->df
        );

        $this->assertInstanceOf(
            'Horde\Injector\Binder\ImplementationTest__NoDependencies',
            $implBinder->create($this->_getInjectorNeverCallMock())
        );
    }

    public function testShouldCreateInstanceOfClassWithTypedDependencies()
    {
        $implBinder = new Implementation(
            'Horde\Injector\Binder\ImplementationTest__TypedDependency',
            $this->df
        );

        $createdInstance = $implBinder->create($this->_getInjectorReturnsNoDependencyObject());

        $this->assertInstanceOf(
            'Horde\Injector\Binder\ImplementationTest__TypedDependency',
            $createdInstance
        );

        $this->assertInstanceOf(
            'Horde\Injector\Binder\ImplementationTest__NoDependencies',
            $createdInstance->dep
        );
    }

    /**
     * @expectedException Horde_Injector_Exception
     */
    public function testShouldThrowExceptionWhenTryingToCreateInstanceOfClassWithUntypedDependencies()
    {
        $implBinder = new Implementation(
            'Horde\Injector\Binder\ImplementationTest__UntypedDependency',
            $this->df
        );

        $implBinder->create($this->_getInjectorNeverCallMock());
    }

    public function testShouldUseDefaultValuesFromUntypedOptionalParameters()
    {
        $implBinder = new Implementation(
            'Horde\Injector\Binder\ImplementationTest__UntypedOptionalDependency',
            $this->df
        );

        $createdInstance = $implBinder->create($this->_getInjectorNeverCallMock());

        $this->assertEquals('DEPENDENCY', $createdInstance->dep);
    }

    /**
     * @expectedException NotFoundException
     */
    public function testShouldThrowExceptionIfRequestedClassIsNotDefined()
    {
        $implBinder = new Implementation(
            'CLASS_DOES_NOT_EXIST',
            $this->df
        );

        $implBinder->create($this->_getInjectorNeverCallMock());
    }

    /**
     * @expectedException NotFoundException
     */
    public function testShouldThrowExceptionIfImplementationIsAnInterface()
    {
        $implBinder = new Implementation(
            'Horde\Injector\Binder\ImplementationTest__Interface',
            $this->df
        );

        $implBinder->create($this->_getInjectorNeverCallMock());
    }

    /**
     * @expectedException Horde_Injector_Exception
     */
    public function testShouldThrowExceptionIfImplementationIsAnAbstractClass()
    {
        $implBinder = new Implementation(
            'Horde\Injector\Binder\ImplementationTest__AbstractClass',
            $this->df
        );

        $implBinder->create($this->_getInjectorNeverCallMock());
    }

    private function _getInjectorNeverCallMock()
    {
        $injector = $this->getMockSkipConstructor('Horde\Injector\Injector', array('getInstance'));
        $injector->expects($this->never())
            ->method('getInstance');
        return $injector;
    }

    private function _getInjectorReturnsNoDependencyObject()
    {
        $injector = $this->getMockSkipConstructor('Horde\Injector\Injector', array('getInstance'));
        $injector->expects($this->once())
            ->method('getInstance')
            ->with($this->equalTo('Horde\Injector\Binder\ImplementationTest__NoDependencies'))
            ->will($this->returnValue(new ImplementationTest__NoDependencies()));
        return $injector;
    }
}

/**
 * Used by preceeding tests!!!
 */

class ImplementationTest__NoDependencies
{
}

class ImplementationTest__TypedDependency
{
    public $dep;

    public function __construct(ImplementationTest__NoDependencies $dep)
    {
        $this->dep = $dep;
    }
}

class ImplementationTest__UntypedDependency
{
    public function __construct($dep)
    {
    }
}

class ImplementationTest__UntypedOptionalDependency
{
    public $dep;

    public function __construct($dep = 'DEPENDENCY')
    {
        $this->dep = $dep;
    }
}

interface ImplementationTest__Interface
{
}

abstract class ImplementationTest__AbstractClass
{
}

class ImplementationTest__SetterNoDependencies
{
    public $setterDep;

    public function setDependency()
    {
        $this->setterDep = 'CALLED';
    }
}

class ImplementationTest__SetterHasDependencies
{
    public $setterDep;

    public function setDependency(ImplementationTest__NoDependencies $setterDep)
    {
        $this->setterDep = $setterDep;
    }
}

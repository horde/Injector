<?php
namespace Horde\Injector\Test\Binder;
use Horde\Injector\Binder;
use Horde\Injector\DependencyFinder;
use Horde\Injector\Injector;
use Horde\Injector\NotFoundException;
use Horde\Injector\TopLevel;
use Horde\Injector\Exception;
use Horde\Injector\Binder\Implementation;

class ImplementationTest extends \Horde\Test\TestCase
{
    public function setUp(): void
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
            ImplementationTest__NoDependencies::class,
            $this->df
        );

        $this->assertInstanceOf(
            ImplementationTest__NoDependencies::class,
            $implBinder->create($this->_getInjectorNeverCallMock())
        );
    }

    public function testShouldCreateInstanceOfClassWithTypedDependencies()
    {
        $implBinder = new Implementation(
            ImplementationTest__TypedDependency::class,
            $this->df
        );

        $createdInstance = $implBinder->create($this->_getInjectorReturnsNoDependencyObject());

        $this->assertInstanceOf(
            ImplementationTest__TypedDependency::class,
            $createdInstance
        );

        $this->assertInstanceOf(
            ImplementationTest__NoDependencies::class,
            $createdInstance->dep
        );
    }

    public function testShouldThrowExceptionWhenTryingToCreateInstanceOfClassWithUntypedDependencies()
    {
        $this->expectException(Exception::class);
        $implBinder = new Implementation(
            ImplementationTest__UntypedDependency::class,
            $this->df
        );

        $implBinder->create($this->_getInjectorNeverCallMock());
    }

    public function testShouldUseDefaultValuesFromUntypedOptionalParameters()
    {
        $implBinder = new Implementation(
            ImplementationTest__UntypedOptionalDependency::class,
            $this->df
        );

        $createdInstance = $implBinder->create($this->_getInjectorNeverCallMock());

        $this->assertEquals('DEPENDENCY', $createdInstance->dep);
    }

    public function testShouldThrowExceptionIfRequestedClassIsNotDefined()
    {
        $this->expectException(NotFoundException::class);
        $implBinder = new Implementation(
            'CLASS_DOES_NOT_EXIST',
            $this->df
        );

        $implBinder->create($this->_getInjectorNeverCallMock());
    }

    public function testShouldThrowExceptionIfImplementationIsAnInterface()
    {
        $this->expectException(NotFoundException::class);
        $implBinder = new Implementation(
            'Horde\Injector\Binder\ImplementationTest__Interface',
            $this->df
        );

        $implBinder->create($this->_getInjectorNeverCallMock());
    }

    public function testShouldThrowExceptionIfImplementationIsAnAbstractClass()
    {
        $this->expectException(Exception::class);
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
            ->with($this->equalTo(ImplementationTest__NoDependencies::class))
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

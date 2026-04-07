<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit\Binder;

use Horde\Injector\Binder\Implementation;
use Horde\Injector\DependencyFinder;
use Horde\Injector\Exception;
use Horde\Injector\Injector;
use Horde\Injector\NotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Implementation::class)]
#[CoversClass(DependencyFinder::class)]
class ImplementationTest extends TestCase
{
    private DependencyFinder $df;

    public function setUp(): void
    {
        $this->df = new DependencyFinder();
    }

    public function testShouldReturnBindingDetails(): void
    {
        $implBinder = new Implementation('IMPLEMENTATION', $this->df);

        $this->assertEquals('IMPLEMENTATION', $implBinder->getImplementation());
    }

    public function testShouldCreateInstanceOfClassWithNoDependencies(): void
    {
        $implBinder = new Implementation(
            ImplementationTestNoDependencies::class,
            $this->df
        );

        $this->assertInstanceOf(
            ImplementationTestNoDependencies::class,
            $implBinder->create($this->getInjectorNeverCallMock())
        );
    }

    public function testShouldCreateInstanceOfClassWithTypedDependencies(): void
    {
        $implBinder = new Implementation(
            ImplementationTestTypedDependency::class,
            $this->df
        );

        $createdInstance = $implBinder->create($this->getInjectorReturnsNoDependencyObject());

        $this->assertInstanceOf(
            ImplementationTestTypedDependency::class,
            $createdInstance
        );

        $this->assertInstanceOf(
            ImplementationTestNoDependencies::class,
            $createdInstance->dep
        );
    }

    public function testShouldThrowExceptionWhenTryingToCreateInstanceOfClassWithUntypedDependencies(): void
    {
        $this->expectException(Exception::class);
        $implBinder = new Implementation(
            ImplementationTestUntypedDependency::class,
            $this->df
        );

        $implBinder->create($this->getInjectorNeverCallMock());
    }

    public function testShouldUseDefaultValuesFromUntypedOptionalParameters(): void
    {
        $implBinder = new Implementation(
            ImplementationTestUntypedOptionalDependency::class,
            $this->df
        );

        $createdInstance = $implBinder->create($this->getInjectorNeverCallMock());

        $this->assertEquals('DEPENDENCY', $createdInstance->dep);
    }

    public function testShouldThrowExceptionIfRequestedClassIsNotDefined(): void
    {
        $this->expectException(NotFoundException::class);
        $implBinder = new Implementation(
            'CLASS_DOES_NOT_EXIST',
            $this->df
        );

        $implBinder->create($this->getInjectorNeverCallMock());
    }

    public function testShouldThrowExceptionIfImplementationIsAnInterface(): void
    {
        $this->expectException(NotFoundException::class);
        $implBinder = new Implementation(
            ImplementationTestInterface::class,
            $this->df
        );

        $implBinder->create($this->getInjectorNeverCallMock());
    }

    public function testShouldThrowExceptionIfImplementationIsAnAbstractClass(): void
    {
        $this->expectException(Exception::class);
        $implBinder = new Implementation(
            ImplementationTestAbstractClass::class,
            $this->df
        );

        $implBinder->create($this->getInjectorNeverCallMock());
    }

    private function getInjectorNeverCallMock(): Injector
    {
        $injector = $this->getMockBuilder(Injector::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getInstance'])
            ->getMock();
        $injector->expects($this->never())
            ->method('getInstance');
        return $injector;
    }

    private function getInjectorReturnsNoDependencyObject(): Injector
    {
        $injector = $this->getMockBuilder(Injector::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getInstance'])
            ->getMock();
        $injector->expects($this->once())
            ->method('getInstance')
            ->with($this->equalTo(ImplementationTestNoDependencies::class))
            ->willReturn(new ImplementationTestNoDependencies());
        return $injector;
    }
}

/**
 * Test fixture classes
 */

class ImplementationTestNoDependencies {}

class ImplementationTestTypedDependency
{
    public ImplementationTestNoDependencies $dep;

    public function __construct(ImplementationTestNoDependencies $dep)
    {
        $this->dep = $dep;
    }
}

class ImplementationTestUntypedDependency
{
    public function __construct($dep) {}
}

class ImplementationTestUntypedOptionalDependency
{
    public string $dep;

    public function __construct($dep = 'DEPENDENCY')
    {
        $this->dep = $dep;
    }
}

interface ImplementationTestInterface {}

abstract class ImplementationTestAbstractClass {}

class ImplementationTestSetterNoDependencies
{
    public ?string $setterDep = null;

    public function setDependency(): void
    {
        $this->setterDep = 'CALLED';
    }
}

class ImplementationTestSetterHasDependencies
{
    public ?ImplementationTestNoDependencies $setterDep = null;

    public function setDependency(ImplementationTestNoDependencies $setterDep): void
    {
        $this->setterDep = $setterDep;
    }
}

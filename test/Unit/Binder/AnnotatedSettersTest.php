<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit\Binder;

use Horde\Injector\Binder;
use Horde\Injector\Binder\AnnotatedSetters;
use Horde\Injector\Binder\Implementation;
use Horde\Injector\DependencyFinder;
use Horde\Injector\Exception;
use Horde\Injector\Injector;
use Horde\Injector\Test\Unit\Fixture\AnInterface;
use Horde\Injector\TopLevel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AnnotatedSetters::class)]
#[CoversClass(DependencyFinder::class)]
class AnnotatedSettersTest extends TestCase
{
    public function testShouldCallAnnotatedSetters(): void
    {
        $instance = new AnnotatedSettersTestTypedSetterDependency();
        $binder = new AnnotatedSettersTestEmptyBinder($instance);
        $df = new DependencyFinder();
        $injector = new Injector(new TopLevel());
        $annotatedSettersBinder = new AnnotatedSetters($binder, $df);

        $this->assertNull($instance->dep);
        $newInstance = $annotatedSettersBinder->create($injector);
        $this->assertInstanceOf(AnnotatedSettersTestNoDependencies::class, $newInstance->dep);
    }

    public function testMultipleAnnotatedSettersAllCalled(): void
    {
        $instance = new MultipleSettersFixture();
        $binder = new AnnotatedSettersTestEmptyBinder($instance);
        $annotatedSettersBinder = new AnnotatedSetters($binder, new DependencyFinder());
        $injector = new Injector(new TopLevel());

        $this->assertNull($instance->dep1);
        $this->assertNull($instance->dep2);
        $newInstance = $annotatedSettersBinder->create($injector);
        $this->assertInstanceOf(AnnotatedSettersTestNoDependencies::class, $newInstance->dep1);
        $this->assertInstanceOf(AnotherNoDependencies::class, $newInstance->dep2);
    }

    public function testSetterWithoutInjectAnnotationNotCalled(): void
    {
        $instance = new NoInjectAnnotationFixture();
        $binder = new AnnotatedSettersTestEmptyBinder($instance);
        $annotatedSettersBinder = new AnnotatedSetters($binder, new DependencyFinder());
        $injector = new Injector(new TopLevel());

        $newInstance = $annotatedSettersBinder->create($injector);
        $this->assertFalse($newInstance->wasCalled);
    }

    public function testPrivateSetterWithInjectNotCalled(): void
    {
        $instance = new PrivateSetterFixture();
        $binder = new AnnotatedSettersTestEmptyBinder($instance);
        $annotatedSettersBinder = new AnnotatedSetters($binder, new DependencyFinder());
        $injector = new Injector(new TopLevel());

        $newInstance = $annotatedSettersBinder->create($injector);
        $this->assertFalse($newInstance->wasCalled());
    }

    public function testSetterWithNoParamsIsCalled(): void
    {
        $instance = new SetterNoParamsFixture();
        $binder = new AnnotatedSettersTestEmptyBinder($instance);
        $annotatedSettersBinder = new AnnotatedSetters($binder, new DependencyFinder());
        $injector = new Injector(new TopLevel());

        $this->assertFalse($instance->wasCalled);
        $newInstance = $annotatedSettersBinder->create($injector);
        $this->assertTrue($newInstance->wasCalled);
    }

    public function testInjectInMiddleOfDocblockDetected(): void
    {
        $instance = new InjectMidDocblockFixture();
        $binder = new AnnotatedSettersTestEmptyBinder($instance);
        $annotatedSettersBinder = new AnnotatedSetters($binder, new DependencyFinder());
        $injector = new Injector(new TopLevel());

        $this->assertNull($instance->dep);
        $newInstance = $annotatedSettersBinder->create($injector);
        $this->assertInstanceOf(AnnotatedSettersTestNoDependencies::class, $newInstance->dep);
    }

    public function testSetterWithUnresolvableDepThrows(): void
    {
        $instance = new SetterUnresolvableDepFixture();
        $binder = new AnnotatedSettersTestEmptyBinder($instance);
        $annotatedSettersBinder = new AnnotatedSetters($binder, new DependencyFinder());
        $injector = new Injector(new TopLevel());

        $this->expectException(\Throwable::class);
        $annotatedSettersBinder->create($injector);
    }

    public function testAnnotatedSettersEquality(): void
    {
        $df = new DependencyFinder();
        $implA = new Implementation('SomeClass', $df);
        $implB = new Implementation('SomeClass', $df);
        $implC = new Implementation('OtherClass', $df);

        $asA = new AnnotatedSetters($implA, $df);
        $asB = new AnnotatedSetters($implB, $df);
        $asC = new AnnotatedSetters($implC, $df);

        $this->assertTrue($asA->equals($asB), 'Same inner binder should be equal');
        $this->assertFalse($asA->equals($asC), 'Different inner binder should not be equal');
        $this->assertFalse($asA->equals($implA), 'AnnotatedSetters should not equal Implementation');
    }
}

/**
 * Test fixture classes
 */

class AnnotatedSettersTestEmptyBinder implements Binder
{
    public function __construct(
        public readonly object $instance
    ) {}

    public function create(Injector $injector): object
    {
        return $this->instance;
    }

    public function equals(Binder $otherBinder): bool
    {
        return false;
    }
}

class AnnotatedSettersTestNoDependencies {}

class AnnotatedSettersTestTypedSetterDependency
{
    public ?AnnotatedSettersTestNoDependencies $dep = null;

    /**
     * @inject
     */
    public function setDep(AnnotatedSettersTestNoDependencies $dep): void
    {
        $this->dep = $dep;
    }
}

class AnotherNoDependencies {}

class MultipleSettersFixture
{
    public ?AnnotatedSettersTestNoDependencies $dep1 = null;
    public ?AnotherNoDependencies $dep2 = null;

    /**
     * @inject
     */
    public function setDep1(AnnotatedSettersTestNoDependencies $dep): void
    {
        $this->dep1 = $dep;
    }

    /**
     * @inject
     */
    public function setDep2(AnotherNoDependencies $dep): void
    {
        $this->dep2 = $dep;
    }
}

class NoInjectAnnotationFixture
{
    public bool $wasCalled = false;

    public function setStuff(AnnotatedSettersTestNoDependencies $dep): void
    {
        $this->wasCalled = true;
    }
}

class PrivateSetterFixture
{
    private bool $called = false;

    /**
     * @inject
     */
    private function setDep(AnnotatedSettersTestNoDependencies $dep): void
    {
        $this->called = true;
    }

    public function wasCalled(): bool
    {
        return $this->called;
    }
}

class SetterNoParamsFixture
{
    public bool $wasCalled = false;

    /**
     * @inject
     */
    public function initialize(): void
    {
        $this->wasCalled = true;
    }
}

class InjectMidDocblockFixture
{
    public ?AnnotatedSettersTestNoDependencies $dep = null;

    /**
     * This setter handles dependency injection.
     *
     * @inject
     * @param AnnotatedSettersTestNoDependencies $dep
     */
    public function setDep(AnnotatedSettersTestNoDependencies $dep): void
    {
        $this->dep = $dep;
    }
}

class SetterUnresolvableDepFixture
{
    /**
     * @inject
     */
    public function setDep(AnInterface $dep): void
    {
    }
}

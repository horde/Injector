<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit\Binder;

use Horde\Injector\Binder;
use Horde\Injector\Binder\AnnotatedSetters;
use Horde\Injector\DependencyFinder;
use Horde\Injector\Injector;
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

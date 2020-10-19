<?php
namespace Horde\Injector\Binder;
use Horde\Injector\Binder;
use Horde\Injector\DependencyFinder;
use Horde\Injector\Injector;
use Horde\Injector\TopLevel;
class AnnotatedSettersTest extends \Horde_Test_Case
{
    public function testShouldCallAnnotatedSetters()
    {
        $instance = new AnnotatedSettersTest__TypedSetterDependency();
        $binder = new AnnotatedSettersTest__EmptyBinder($instance);
        $df = new DependencyFinder();
        $injector = new Injector(new TopLevel());
        $annotatedSettersBinder = new AnnotatedSetters($binder, $df);

        $this->assertNull($instance->dep);
        $newInstance = $annotatedSettersBinder->create($injector);
        $this->assertInstanceOf('Horde\Injector\Binder\AnnotatedSettersTest__NoDependencies', $newInstance->dep);
    }
}

/**
 * Used by preceeding tests!!!
 */

class AnnotatedSettersTest__EmptyBinder implements Binder
{
    public $instance;
    public function __construct($instance)
    {
        $this->instance = $instance;
    }

    public function create(Injector $injector)
    {
        return $this->instance;
    }

    public function equals(Binder $otherBinder): bool
    {
        return false;
    }
}

class AnnotatedSettersTest__NoDependencies
{
}

class AnnotatedSettersTest__TypedSetterDependency
{
    public $dep;

    /**
     * @inject
     */
    public function setDep(AnnotatedSettersTest__NoDependencies $dep)
    {
        $this->dep = $dep;
    }
}

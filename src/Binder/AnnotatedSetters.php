<?php
/**
 * Copyright 2009-2020 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (BSD). If you
 * did not receive this file, see http://www.horde.org/licenses/bsd.
 *
 * @category  Horde
 * @copyright 2009-2020 Horde LLC
 * @license   http://www.horde.org/licenses/bsd BSD
 * @package   Injector
 */
namespace Horde\Injector\Binder;

use \Horde\Injector\Binder;
use \Horde\Injector\Exception;
use \Horde\Injector\Injector;
use \Horde\Injector\DependencyFinder;
/**
 * This is a binder that finds methods marked with @inject and calls them with
 * their dependencies. It must be stacked on another binder that actually
 * creates the instance.
 *
 * @author    Bob Mckee <bmckee@bywires.com>
 * @author    James Pepin <james@jamespepin.com>
 * @author    Chuck Hagenbuch <chuck@horde.org>
 * @category  Horde
 * @copyright 2009-2020 Horde LLC
 * @license   http://www.horde.org/licenses/bsd BSD
 * @package   Injector
 */
class AnnotatedSetters implements Binder
{
    /**
     * @var Binder
     */
    private $binder;

    /**
     * @var DependencyFinder
     */
    private $dependencyFinder;

    /**
     * Constructor.
     *
     * @param \Horde\Injector\Binder $binder
     * @param \Horde\Injector\DependencyFinder $finder
     *
     */
    public function __construct(Binder $binder,
                                DependencyFinder $finder = null)
    {
        $this->binder = $binder;
        $this->dependencyFinder = is_null($finder)
            ? new DependencyFinder()
            : $finder;
    }

    /**
     * @param Binder $binder
     *
     * @return bool  Equality.
     */
    public function equals(Binder $otherBinder): bool
    {
        return ($otherBinder instanceof AnnotatedSetters) &&
            $this->getBinder()->equals($otherBinder->getBinder());
    }

    /**
     * @return Binder
     */
    public function getBinder(): ?Binder
    {
        return $this->binder;
    }

    /**
     * @param Injector
     */
    public function create(Injector $injector)
    {
        $instance = $this->binder->create($injector);

        try {
            $reflectionClass = new \ReflectionClass(get_class($instance));
        } catch (\ReflectionException $e) {
            throw new Exception($e);
        }
        $setters = $this->findAnnotatedSetters($reflectionClass);
        $this->callSetters($setters, $injector, $instance);

        return $instance;
    }

    /**
     * Find all public methods in $reflectionClass that are annotated with
     * @inject.
     *
     * @param \ReflectionClass $reflectionClass
     *
     * @return array
     */
    private function findAnnotatedSetters(\ReflectionClass $reflectionClass): array
    {
        $setters = [];
        foreach ($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
            if ($this->isSetterMethod($reflectionMethod)) {
                $setters[] = $reflectionMethod;
            }
        }

        return $setters;
    }

    /**
     * Is a method a setter method, by the criteria we define (has a doc
     * comment that includes @inject).
     *
     * @param \ReflectionMethod $reflectionMethod
     * 
     * @return bool 
     */
    private function isSetterMethod(\ReflectionMethod $reflectionMethod): bool
    {
        $docBlock = $reflectionMethod->getDocComment();
        if ($docBlock) {
            if (strpos($docBlock, '@inject') !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Call each ReflectionMethod in the $setters array, filling in its
     * dependencies with the $injector.
     *
     * @param array $setters            Array of ReflectionMethods to call.
     * @param Injector $injector        The injector to get dependencies from.
     * @param object $instance          The object to call setters on.
     */
    private function callSetters(array $setters, Injector $injector,
                                  $instance)
    {
        foreach ($setters as $setterMethod) {
            $setterMethod->invokeArgs(
                $instance,
                $this->dependencyFinder->getMethodDependencies($injector, $setterMethod)
            );
        }
    }

}

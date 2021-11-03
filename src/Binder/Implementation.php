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

use Horde\Injector\Binder;
use Horde\Injector\DependencyFinder;
use Horde\Injector\Exception;
use Horde\Injector\NotFoundException;
use Horde\Injector\Injector;
/**
 * @author    Bob Mckee <bmckee@bywires.com>
 * @author    James Pepin <james@jamespepin.com>
 * @category  Horde
 * @copyright 2009-2020 Horde LLC
 * @license   http://www.horde.org/licenses/bsd BSD
 * @package   Injector
 */
class Implementation implements Binder
{
    /**
     * @var mixed
     */
    private $implementation;

    /**
     * @var DependencyFinder
     */
    private $dependencyFinder;

    /**
     */
    public function __construct($implementation,
                                DependencyFinder $finder = null)
    {
        $this->implementation = $implementation;
        $this->dependencyFinder = is_null($finder)
            ? new DependencyFinder()
            : $finder;
    }

    /**
     * @return mixed
     */
    public function getImplementation()
    {
        return $this->implementation;
    }

    /**
     * @return bool  Equality.
     */
    public function equals(Binder $otherBinder): bool
    {
        return (($otherBinder instanceof Implementation) &&
                ($otherBinder->getImplementation() == $this->implementation));
    }

    /**
     * @param Injector
     */
    public function create(Injector $injector)
    {
        try {
            $reflectionClass = new \ReflectionClass($this->implementation);
        } catch (\ReflectionException $e) {
            throw new NotFoundException($e);
        }
        $this->validateImplementation($reflectionClass);
        return $this->getInstance($injector, $reflectionClass);
    }

    /**
     */
    protected function validateImplementation(\ReflectionClass $reflectionClass)
    {
        if ($reflectionClass->isAbstract() || $reflectionClass->isInterface()) {
            throw new NotFoundException('Cannot bind interface or abstract class "' . $this->implementation . '" to an interface.');
        }
    }

    /**
     */
    protected function getInstance(Injector $injector,
                                    \ReflectionClass $class)
    {
        return $class->getConstructor()
            ? $class->newInstanceArgs($this->dependencyFinder->getMethodDependencies($injector, $class->getConstructor()))
            : $class->newInstance();
    }

}

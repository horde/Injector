<?php
/**
 * Copyright 2009-2021 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (BSD). If you
 * did not receive this file, see http://www.horde.org/licenses/bsd.
 *
 * @category  Horde
 * @copyright 2009-2021 Horde LLC
 * @license   http://www.horde.org/licenses/bsd BSD
 * @package   Injector
 */
namespace Horde\Injector\Binder;

use Horde\Injector\Binder;
use Horde\Injector\Injector;
/**
 * A binder object for binding an interface to a factory class and method.
 *
 * An interface may be bound to a factory class.  That factory class must
 * provide a method or methods that accept a Horde\Injector\Injector, and return an
 * object that satisfies the instance requirement. For example:
 *
 * <pre>
 * class MyFactory {
 *   ...
 *   public function create(Injector $injector)
 *   {
 *     return new MyClass($injector->getInstance('Collaborator'), new MyOtherClass(17));
 *   }
 *   ...
 * }
 * </pre>
 *
 * @author    Bob Mckee <bmckee@bywires.com>
 * @author    James Pepin <james@jamespepin.com>
 * @category  Horde
 * @copyright 2009-2021 Horde LLC
 * @license   http://www.horde.org/licenses/bsd BSD
 * @package   Injector
 */
class Factory implements Binder
{
    /**
     * @var string
     */
    private $factory;

    /**
     * @var string
     */
    private $method;

    /**
     * Create a new Horde\Injector\Binder\Factory instance.
     *
     * @param string $factory  The factory class to use for creating objects.
     * @param string $method   The method on that factory to use for creating
     *                         objects.
     */
    public function __construct($factory, $method)
    {
        $this->factory = $factory;
        $this->method = $method;
    }

    /**
     * @param Binder $otherBinder
     *
     * @return bool  Equality.
     */
    public function equals(Binder $otherBinder): bool
    {
        return (($otherBinder instanceof Factory) &&
                ($otherBinder->getFactory() == $this->factory) &&
                ($otherBinder->getMethod() == $this->method));
    }

    /**
     * Get the factory classname that this binder was bound to.
     *
     * @return string  The factory classname this binder is bound to.
     */
    public function getFactory(): ?string
    {
        return $this->factory;
    }

    /**
     * Get the method that this binder was bound to.
     *
     * @return string  The method this binder is bound to.
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }

    /**
     * Create instance using a factory method
     *
     * If the factory depends on a Injector we want to limit its scope
     * so it cannot change anything that effects any higher-level scope.  A
     * factory should not have the responsibility of making a higher-level
     * scope change.
     * To enforce this we create a new child Injector.  When an
     * Injector is requested from an Injector it will return
     * itself. This means that the factory will only ever be able to work on
     * the child Injector we give it now.
     *
     * @param Injector $injector  Injector object.
     *
     * @return object A factory
     */
    public function create(Injector $injector)
    {
        $childInjector = $injector->createChildInjector();

        /* We use getInstance() here because we don't want to have to create
         * this factory more than one time to create more objects of this
         * type. */
        return $childInjector->getInstance($this->factory)->{$this->method}($childInjector);
    }

}

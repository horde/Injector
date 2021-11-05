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
 * A binder object for binding an interface to a closure.
 *
 * An interface may be bound to a closure.  That closure must accept a
 * Horde\Injector and return an object that satisfies the instance
 * requirement. For example:
 *
 * <pre>
 * $injector->bindClosure('database', function($injector) { return new my_mysql(); });
 * </pre>
 *
 * @author    Bob Mckee <bmckee@bywires.com>
 * @author    James Pepin <james@jamespepin.com>
 * @author    Chuck Hagenbuch <chuck@horde.org>
 * @category  Horde
 * @copyright 2009-2021 Horde LLC
 * @license   http://www.horde.org/licenses/bsd BSD
 * @package   Injector
 */
class Closure implements Binder
{
    /**
     * @var \Closure
     */
    private $closure;

    /**
     * Create a new Closure instance.
     *
     * @param \Closure $closure  The closure to use for creating objects.
     */
    public function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }

    /**
     * @param Binder $otherBinder
     *
     * @return boolean  Equality.
     */
    public function equals(Binder $otherBinder): bool
    {
        return (($otherBinder instanceof Closure) &&
                ($otherBinder->getClosure() == $this->closure));
    }

    /**
     * Get the closure that this binder was bound to.
     *
     * @return callable  The closure this binder is bound to.
     */
    public function getClosure(): ?callable
    {
        return $this->closure;
    }

    /**
     * Create instance using a closure.
     *
     * If the closure depends on a Horde\Injector we want to limit its scope
     * so it cannot change anything that effects any higher-level scope.  A
     * closure should not have the responsibility of making a higher-level
     * scope change.
     * To enforce this we create a new child Horde\Injector\Injector.  When a
     * Injector is requested from a Injector it will return
     * itself. This means that the closure will only ever be able to work on
     * the child Injector we give it now.
     *
     * @param Injector $injector  Injector object.
     *
     * @return mixed The object or value created from the closure.
     */
    public function create(Injector $injector)
    {
        $childInjector = $injector->createChildInjector();
        $closure = $this->closure;

        return $closure($childInjector);
    }
}

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

namespace Horde\Injector;

use Horde\Injector\Binder;
use Horde\Injector\Binder\AnnotatedSetters;

/**
 * Top level injector class for returning the default binding for an object
 *
 * This class returns a Horde\Injector\Binder\Implementation with the
 * requested $interface mapped to itself.  This is the default case, and for
 * concrete classes should work all the time so long as you constructor
 * parameters are typed.
 *
 * @author    Bob Mckee <bmckee@bywires.com>
 * @author    James Pepin <james@jamespepin.com>
 * @category  Horde
 * @copyright 2009-2021 Horde LLC
 * @license   http://www.horde.org/licenses/bsd BSD
 * @package   Injector
 */
class TopLevel implements Scope
{
    /**
     * Get an Implementation Binder that maps the $interface to itself.
     *
     * @param string $interface  The interface to retrieve binding information
     *                           for.
     *
     * @return AnnotatedSetters
     *         A new binding object that maps the interface to itself.
     */
    public function getBinder(string $interface): ?Binder
    {
        $dependencyFinder = new DependencyFinder();
        $implementationBinder = new Binder\Implementation($interface, $dependencyFinder);

        return new Binder\AnnotatedSetters($implementationBinder, $dependencyFinder);
    }

    /**
     * Always return null.  Object doesn't keep instance references.
     *
     * Method is necessary because this object is the default parent Injector.
     * The child of this injector will ask it for instances in the case where
     * no bindings are set on the child.  This should always return null.
     *
     * @param string $interface  The interface in question.
     *
     * @return null
     */
    public function getInstance(string $interface)
    {
        return null;
    }

    public function get($interface)
    {
        return null;
    }

    /**
     * Stub of has()
     * 
     * Always false.
     *
     * @param string $interface
     * @return bool False
     */
    public function has(string $interface): bool
    {
        return false;
    }
}

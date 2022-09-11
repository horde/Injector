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

/**
 * Interface for injector scopes
 *
 * Injectors implement a Chain of Responsibility pattern.  This is the
 * required interface for injectors to pass on responsibility to parent
 * objects in the chain.
 *
 * @category  Horde
 * @copyright 2009-2021 Horde LLC
 * @license   http://www.horde.org/licenses/bsd BSD
 * @package   Injector
 */
interface Scope
{
    /**
     * Returns the Horde\Injector\Binder object mapped to the request
     * interface if such a
     * mapping exists
     *
     * @param string $interface  Interface name of object whose binding if
     *                           being retrieved.
     *
     * @return Binder|null
     */
    public function getBinder(string $interface): ?Binder;

    /**
     * Returns instance of requested object if proper configuration has been
     * provided.
     *
     * @param string $interface  Interface name of object which is being
     *                           requested.
     *
     * @return mixed
     */
    public function getInstance(string $interface);

    /**
     * Returns instance of requested object if proper configuration has been
     * provided.
     *
     * @param string $interface  Interface name of object which is being
     *                           requested.
     *
     * @return mixed
     */
    public function get(string $interface);

    /**
     * 
     *
     * @param string $interface
     * @return boolean
     */
    public function has(string $interface): bool;
}

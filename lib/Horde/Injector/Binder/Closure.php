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
require_once(dirname(__FILE__, 4) . '/_autoload.php');
use Horde\Injector\Binder\Closure;
/**
 * A binder object for binding an interface to a closure.
 *
 * An interface may be bound to a closure.  That closure must accept a
 * Horde_Injector and return an object that satisfies the instance
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
class Horde_Injector_Binder_Closure extends Closure implements \Horde_Injector_Binder
{
}

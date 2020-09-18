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
require_once(dirname(__FILE__, 2) .'/_autoload.php');
use Horde\Injector\Injector;

/**
 * Injector class for injecting dependencies of objects
 *
 * This class is responsible for injecting dependencies of objects.  It is
 * inspired by the bucket_Container's concept of child scopes, but written to
 * support many different types of bindings as well as allowing for setter
 * injection bindings.
 *
 * @author    Bob Mckee <bmckee@bywires.com>
 * @author    James Pepin <james@jamespepin.com>
 * @category  Horde
 * @copyright 2009-2020 Horde LLC
 * @license   http://www.horde.org/licenses/bsd BSD
 * @package   Injector
 */
class Horde_Injector extends Injector implements \Horde_Injector_Scope
{
}

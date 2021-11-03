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
require_once(dirname(__FILE__, 3) . '/_autoload.php');
use Horde\Injector\TopLevel;

/**
 * Top level injector class for returning the default binding for an object
 *
 * This class returns a Horde_Injector_Binder_Implementation with the
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
class Horde_Injector_TopLevel extends TopLevel implements \Horde_Injector_Scope
{
}

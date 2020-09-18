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
require_once('../../../../src/Binder/Factory.php');
use Horde\Injector\Binder\Factory;

/**
 * A binder object for binding an interface to a factory class and method.
 *
 * An interface may be bound to a factory class.  That factory class must
 * provide a method or methods that accept a Horde_Injector, and return an
 * object that satisfies the instance requirement. For example:
 *
 * <pre>
 * class MyFactory {
 *   ...
 *   public function create(Horde_Injector $injector)
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
 * @copyright 2009-2020 Horde LLC
 * @license   http://www.horde.org/licenses/bsd BSD
 * @package   Injector
 */
class Horde_Injector_Binder_Factory extends Factory implements \Horde_Injector_Binder
{
}

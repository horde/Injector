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
require_once(dirname(__FILE__, 3) . '/_autoload.php');
use Horde\Injector\Scope;

/**
 * Interface for injector scopes
 *
 * Injectors implement a Chain of Responsibility pattern.  This is the
 * required interface for injectors to pass on responsibility to parent
 * objects in the chain.
 *
 * @category  Horde
 * @copyright 2009-2020 Horde LLC
 * @license   http://www.horde.org/licenses/bsd BSD
 * @package   Injector
 */
interface Horde_Injector_Scope extends Scope
{
}

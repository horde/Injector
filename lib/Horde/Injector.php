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

    public function __construct(\Horde_Injector_Scope $scope)
    {
        parent::__construct($scope);
        $this->setInstance('Horde_Injector', $this);
        $this->setInstance('\Horde_Injector', $this);
    }

    /**
     * Create a child injector that inherits this injector's scope.
     *
     * All child injectors inherit the parent scope.  Any objects that were
     * created using getInstance, will be available to the child container.
     * The child container can set bindings to override the parent, and none
     * of those bindings will leak to the parent.
     *
     * @return Injector  A child injector with $this as its parent.
     */
    public function createChildInjector(): Injector
    {
        // Using self is wrong and breaks wrapping into inheriting injectors
        return new self($this);
    }
}

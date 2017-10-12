<?php
/**
 * Demonstrates settings/getting concrete instances.
 *
 * PHP version 5
 *
 * @category Horde
 * @package  Injector
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.horde.org/licenses/bsd BSD
 */

require 'Horde/Autoloader.php';

$a = new Horde_Injector(new Horde_Injector_TopLevel());
$a->setInstance('a', 'a');
var_dump($a->getInstance('a'));

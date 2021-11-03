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
use Psr\Container\NotFoundExceptionInterface;
/**
 * Injector NotFoundException class as suggested by PSR-11.
 *
 * @author    Ralf Lang <lang@b1-systems.de>
 * @category  Horde
 * @copyright 2020-2021 Horde LLC
 * @license   http://www.horde.org/licenses/bsd BSD
 * @package   Injector
 */
class NotFoundException extends Exception implements NotFoundExceptionInterface
{
}
<?php
declare(strict_types=1);

/**
 * Copyright 2026 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (BSD). If you
 * did not receive this file, see http://www.horde.org/licenses/bsd.
 *
 * @category  Horde
 * @copyright 2026 The Horde Project
 * @license   http://www.horde.org/licenses/bsd BSD
 * @package   Injector
 */

namespace Horde\Injector\Attribute;

use Attribute;

/**
 * Marks a class or factory method as using factory-based instantiation.
 *
 * When applied to a target class, specifies which factory class and method
 * should be used to create instances via the injector.
 *
 * When applied to a factory method, specifies which class this method creates.
 *
 * @category  Horde
 * @copyright 2026 The Horde Project
 * @license   http://www.horde.org/licenses/bsd BSD
 * @package   Injector
 * @since     3.0.0
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Factory
{
    /**
     * @param string|null $factory Factory class name (when on target class)
     * @param string|null $method  Factory method name (when on target class)
     * @param string|null $creates Target class this factory creates (when on factory method)
     */
    public function __construct(
        public readonly ?string $factory = null,
        public readonly ?string $method = null,
        public readonly ?string $creates = null,
    ) {
    }
}

<?php

declare(strict_types=1);

/**
 * Copyright 2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (BSD). If you
 * did not receive this file, see http://www.horde.org/licenses/bsd.
 */

namespace Horde\Injector\Event;

/**
 * Dispatched when a binding is registered via addBinder().
 */
final readonly class BindingRegistered
{
    public function __construct(
        public string $interface,
        public string $binderType,
        public ?string $implementation,
    ) {}
}

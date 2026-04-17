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
 * Dispatched when get()/getInstance() returns a dependency.
 */
final readonly class DependencyResolved
{
    public function __construct(
        public string $interface,
        public bool $fromCache,
        public string $source,
    ) {}
}

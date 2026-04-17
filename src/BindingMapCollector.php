<?php

declare(strict_types=1);

/**
 * Copyright 2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (BSD). If you
 * did not receive this file, see http://www.horde.org/licenses/bsd.
 */

namespace Horde\Injector;

use Horde\Injector\Event\BindingRegistered;

/**
 * Callable listener that accumulates binding data from BindingRegistered events.
 *
 * Register with a PSR-14 ListenerProvider, then attach the dispatcher
 * to the Injector via setEventDispatcher(). After exercising the app,
 * read back the collected bindings.
 */
class BindingMapCollector
{
    /** @var array<string, array{type: string, implementation: ?string}> */
    private array $bindings = [];

    /** @var string[] Interfaces bound via Closure (not opcacheable) */
    private array $uncacheable = [];

    public function __invoke(object $event): void
    {
        if (!$event instanceof BindingRegistered) {
            return;
        }

        $this->bindings[$event->interface] = [
            'type' => $event->binderType,
            'implementation' => $event->implementation,
        ];

        if ($event->binderType === 'Closure') {
            $this->uncacheable[] = $event->interface;
        }
    }

    /**
     * @return array<string, array{type: string, implementation: ?string}>
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * @return string[] Interfaces with non-cacheable (Closure) bindings.
     */
    public function getUncacheable(): array
    {
        return array_values(array_unique($this->uncacheable));
    }

    public function reset(): void
    {
        $this->bindings = [];
        $this->uncacheable = [];
    }
}

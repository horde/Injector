<?php

declare(strict_types=1);

/**
 * Copyright 2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (BSD). If you
 * did not receive this file, see http://www.horde.org/licenses/bsd.
 */

namespace Horde\Injector;

use Horde\Injector\Binder\AnnotatedSetters;
use Horde\Injector\Binder\Closure as ClosureBinder;
use Horde\Injector\Binder\Factory;
use Horde\Injector\Binder\Implementation;

/**
 * Extracts bindings from an Injector and writes them as a
 * loadBindings()-compatible PHP array file.
 */
class BindingMapWriter
{
    /**
     * Extract bindings from an Injector's current state.
     *
     * Inspects each registered binder and converts it to the
     * loadBindings() array format. Closure bindings cannot be
     * serialized and are returned separately.
     *
     * @return array{cacheable: array<string, string|array{0: string, 1: string}>, uncacheable: string[]}
     */
    public function extractBindings(Injector $injector): array
    {
        $cacheable = [];
        $uncacheable = [];

        foreach ($injector->getBindings() as $interface => $binder) {
            $binder = $this->unwrap($binder);

            if ($binder instanceof Implementation) {
                $cacheable[$interface] = $binder->getImplementation();
            } elseif ($binder instanceof Factory) {
                $cacheable[$interface] = [$binder->getFactory(), $binder->getMethod()];
            } elseif ($binder instanceof ClosureBinder) {
                $uncacheable[] = $interface;
            }
        }

        return ['cacheable' => $cacheable, 'uncacheable' => $uncacheable];
    }

    /**
     * Write cacheable bindings to a PHP file that returns an array.
     *
     * The output file is suitable for opcaching and consumption
     * by Injector::loadBindings().
     */
    public function write(string $filePath, array $cacheableBindings): void
    {
        $lines = ["<?php\n", "declare(strict_types=1);\n", "return [\n"];

        foreach ($cacheableBindings as $interface => $definition) {
            $key = var_export($interface, true);
            if (is_string($definition)) {
                $lines[] = "    $key => " . var_export($definition, true) . ",\n";
            } elseif (is_array($definition)) {
                $factory = var_export($definition[0], true);
                $method = var_export($definition[1], true);
                $lines[] = "    $key => [$factory, $method],\n";
            }
        }

        $lines[] = "];\n";

        file_put_contents($filePath, implode('', $lines));
    }

    /**
     * Unwrap AnnotatedSetters decorator to get the inner binder.
     */
    private function unwrap(Binder $binder): Binder
    {
        while ($binder instanceof AnnotatedSetters) {
            $inner = $binder->getBinder();
            if ($inner === null) {
                break;
            }
            $binder = $inner;
        }
        return $binder;
    }
}

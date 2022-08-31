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

use ReflectionNamedType;
use ReflectionUnionType;
use ReflectionClass;

/**
 * This is a simple class that uses reflection to figure out the dependencies
 * of a method and attempts to return them using the Injector instance.
 *
 * @author    Bob Mckee <bmckee@bywires.com>
 * @author    James Pepin <james@jamespepin.com>
 * @author    Chuck Hagenbuch <chuck@horde.org>
 * @category  Horde
 * @copyright 2009-2021 Horde LLC
 * @license   http://www.horde.org/licenses/bsd BSD
 * @package   Injector
 */
class DependencyFinder
{
    /**
     * @param Injector $injector
     * @param \ReflectionMethod $method
     *
     * @return mixed[]
     * @throws Exception
     */
    public function getMethodDependencies(
        Injector $injector,
        \ReflectionMethod $method
    ): array {
        $dependencies = [];

        try {
            foreach ($method->getParameters() as $parameter) {
                $dependencies[] = $this->getParameterDependency($injector, $parameter);
            }
        } catch (Exception $e) {
            throw new Exception("$method has unfulfilled dependencies ($parameter)", 0, $e);
        }

        return $dependencies;
    }

    /**
     * @param Injector $injector
     * @param \ReflectionParameter $parameter
     *
     * @return mixed
     * @throws Exception
     */
    public function getParameterDependency(
        Injector $injector,
        \ReflectionParameter $parameter
    ) {
        $type = $parameter->getType();
        // TODO: What about union and intersection types?
        if ($type instanceof ReflectionNamedType && !$type->isBuiltin() && $classname = $type->getName()) {
            return $injector->getInstance($classname);
        }
        // Catch optional array parameters
        // TODO: What about union and intersection types including arrays?
        if ($type instanceof ReflectionNamedType && $type->getName() === 'array' && $parameter->isOptional()) {
            return $parameter->getDefaultValue();
        }
        // Handle typed parameters other than arrays
        $type = $parameter->getType();
        if ($type instanceof ReflectionNamedType) {
            $types = [$type];
        } elseif ($type instanceof ReflectionUnionType) {
            $types = $type->getTypes();
        } else {
            $types = [];
        }

        foreach ($types as $type) {
            if ($type instanceof ReflectionNamedType && !in_array($type->getName(), ['bool', 'int', 'string', 'float'])) {
                $instance = $injector->getInstance($type);
                if ($instance) {
                    return $instance;
                }
                $instance = $injector->getInstance('\\' . $type);
                if ($instance) {
                    return $instance;
                }
            }
        }

        if ($parameter->isOptional()) {
            return $parameter->getDefaultValue();
        }

        throw new Exception("Untyped parameter \$" . $parameter->getName() . "can't be fulfilled");
    }
}

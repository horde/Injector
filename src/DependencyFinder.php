<?php

/**
 * Copyright 2009-2026 Horde LLC (http://www.horde.org/)
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

use Throwable;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionUnionType;
use ReflectionMethod;
use ReflectionParameter;

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
     * @param ReflectionMethod $method
     *
     * @return mixed[]
     * @throws Exception
     */
    public function getMethodDependencies(
        Injector $injector,
        ReflectionMethod $method
    ): array {
        $dependencies = [];

        try {
            foreach ($method->getParameters() as $parameter) {
                if ($parameter->isVariadic()) {
                    break;
                }
                $dependencies[] = $this->getParameterDependency($injector, $parameter);
            }
        } catch (CircularDependencyException $e) {
            // Re-throw circular dependency as-is
            throw $e;
        } catch (Exception $e) {
            $className = $method->getDeclaringClass()->getName();
            $methodName = $method->getName();
            $paramName = $parameter->getName();
            $paramType = $parameter->getType() ? (string) $parameter->getType() : 'untyped';

            throw new Exception(
                sprintf(
                    'Cannot resolve parameter $%s (%s) for %s::%s()',
                    $paramName,
                    $paramType,
                    $className,
                    $methodName
                ),
                0,
                $e
            );
        }

        return $dependencies;
    }

    /**
     * @param Injector $injector
     * @param ReflectionParameter $parameter
     *
     * @return mixed
     * @throws Exception
     */
    public function getParameterDependency(
        Injector $injector,
        ReflectionParameter $parameter
    ) {
        $type = $parameter->getType();
        // Single named type: resolve directly or fall back to default if optional
        if ($type instanceof ReflectionNamedType && !$type->isBuiltin() && $classname = $type->getName()) {
            try {
                return $injector->getInstance($classname);
            } catch (Throwable $e) {
                if ($parameter->isOptional()) {
                    return $parameter->getDefaultValue();
                }
                throw $e;
            }
        }
        // Catch optional array parameters
        if ($type instanceof ReflectionNamedType && $type->getName() === 'array' && $parameter->isOptional()) {
            return $parameter->getDefaultValue();
        }
        // Handle typed parameters other than arrays
        $type = $parameter->getType();
        if ($type instanceof ReflectionNamedType) {
            $types = [$type];
        } elseif ($type instanceof ReflectionUnionType) {
            $types = $type->getTypes();
        } elseif ($type instanceof ReflectionIntersectionType) {
            return $this->resolveIntersectionType($injector, $parameter, $type);
        } else {
            $types = [];
        }

        foreach ($types as $type) {
            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                try {
                    return $injector->getInstance($type->getName());
                } catch (Throwable) {
                    continue;
                }
            }
        }

        if ($parameter->isOptional()) {
            return $parameter->getDefaultValue();
        }

        $paramName = $parameter->getName();
        $paramType = $parameter->getType() ? (string) $parameter->getType() : 'untyped';

        throw new Exception(
            sprintf(
                'Parameter $%s (%s) cannot be resolved',
                $paramName,
                $paramType
            )
        );
    }

    /**
     * Resolve an intersection type by trying each member interface.
     *
     * For each named member, attempt getInstance(). If the resolved
     * instance satisfies all member interfaces, return it.
     * Falls back to the parameter default if optional.
     */
    private function resolveIntersectionType(
        Injector $injector,
        ReflectionParameter $parameter,
        ReflectionIntersectionType $intersectionType,
    ): mixed {
        $memberNames = [];
        foreach ($intersectionType->getTypes() as $memberType) {
            if ($memberType instanceof ReflectionNamedType && !$memberType->isBuiltin()) {
                $memberNames[] = $memberType->getName();
            }
        }

        foreach ($memberNames as $name) {
            try {
                $instance = $injector->getInstance($name);
            } catch (Throwable) {
                continue;
            }
            // Verify the instance satisfies all members
            $satisfiesAll = true;
            foreach ($memberNames as $requiredName) {
                if (!($instance instanceof $requiredName)) {
                    $satisfiesAll = false;
                    break;
                }
            }
            if ($satisfiesAll) {
                return $instance;
            }
        }

        if ($parameter->isOptional()) {
            return $parameter->getDefaultValue();
        }

        $paramName = $parameter->getName();
        $paramType = (string) $parameter->getType();

        throw new Exception(
            sprintf(
                'Parameter $%s (%s) cannot be resolved',
                $paramName,
                $paramType
            )
        );
    }
}

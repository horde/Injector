<?php

declare(strict_types=1);

/**
 * Copyright 2026-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (BSD). If you
 * did not receive this file, see http://www.horde.org/licenses/bsd.
 *
 * @category  Horde
 * @copyright 2026-2026 Horde LLC
 * @license   http://www.horde.org/licenses/bsd BSD
 * @package   Injector
 */

namespace Horde\Injector\Test\Unit;

use Horde\Injector\CircularDependencyException;
use Horde\Injector\Injector;
use Horde\Injector\TopLevel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Injector::class)]
class CircularDependencyTest extends TestCase
{
    public function testDirectCircularDependencyDetected(): void
    {
        $injector = new Injector(new TopLevel());

        $this->expectException(CircularDependencyException::class);
        $this->expectExceptionMessage('Circular dependency detected');
        $this->expectExceptionMessage('CircularA');
        $this->expectExceptionMessage('CircularB');

        $injector->get(CircularA::class);
    }

    public function testSelfReferenceDetected(): void
    {
        $injector = new Injector(new TopLevel());

        $this->expectException(CircularDependencyException::class);
        $this->expectExceptionMessage('Circular dependency detected');
        $this->expectExceptionMessage('SelfReferencing');

        $injector->get(SelfReferencing::class);
    }

    public function testLongCircularChainDetected(): void
    {
        $injector = new Injector(new TopLevel());

        $this->expectException(CircularDependencyException::class);
        $this->expectExceptionMessage('Circular dependency detected');
        $this->expectExceptionMessage('ChainA');
        $this->expectExceptionMessage('ChainB');
        $this->expectExceptionMessage('ChainC');

        $injector->get(ChainA::class);
    }

    public function testNonCircularDependencyWorks(): void
    {
        $injector = new Injector(new TopLevel());

        // This should work fine - no circular dependency
        $service = $injector->get(LinearA::class);

        $this->assertInstanceOf(LinearA::class, $service);
    }

    public function testResolutionStackCleanedUpAfterSuccess(): void
    {
        $injector = new Injector(new TopLevel());

        // First resolution should work
        $service1 = $injector->get(LinearA::class);
        $this->assertInstanceOf(LinearA::class, $service1);

        // Second resolution should work (stack was cleaned up)
        $service2 = $injector->get(LinearA::class);
        $this->assertInstanceOf(LinearA::class, $service2);

        // Should get same instance (cached)
        $this->assertSame($service1, $service2);
    }

    public function testResolutionStackCleanedUpAfterException(): void
    {
        $injector = new Injector(new TopLevel());

        // First attempt fails with circular dependency
        try {
            $injector->get(CircularA::class);
            $this->fail('Should have thrown CircularDependencyException');
        } catch (CircularDependencyException $e) {
            $this->assertStringContainsString('Circular dependency detected', $e->getMessage());
        }

        // Second attempt should also fail with same error (stack was cleaned up)
        try {
            $injector->get(CircularA::class);
            $this->fail('Should have thrown CircularDependencyException');
        } catch (CircularDependencyException $e) {
            $this->assertStringContainsString('Circular dependency detected', $e->getMessage());
        }
    }
}

// Test fixtures - Circular dependencies
class CircularA
{
    public function __construct(CircularB $b) {}
}

class CircularB
{
    public function __construct(CircularA $a) {}
}

class SelfReferencing
{
    public function __construct(SelfReferencing $self) {}
}

class ChainA
{
    public function __construct(ChainB $b) {}
}

class ChainB
{
    public function __construct(ChainC $c) {}
}

class ChainC
{
    public function __construct(ChainA $a) {}
}

// Test fixtures - Linear (non-circular) dependencies
class LinearA
{
    public function __construct(LinearB $b) {}
}

class LinearB
{
    public function __construct(LinearC $c) {}
}

class LinearC
{
    public function __construct() {}
}

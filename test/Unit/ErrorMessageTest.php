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

use Horde\Injector\Injector;
use Horde\Injector\NotFoundException;
use Horde\Injector\TopLevel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Injector::class)]
class ErrorMessageTest extends TestCase
{
    public function testDeepDependencyChainErrorMessage(): void
    {
        $injector = new Injector(new TopLevel());

        try {
            $injector->get(DeepTopLevel::class);
            $this->fail('Should have thrown NotFoundException');
        } catch (NotFoundException $e) {
            $message = $e->getMessage();

            // Should mention what was requested
            $this->assertStringContainsString('Cannot create', $message);
            $this->assertStringContainsString('DeepTopLevel', $message);

            // Should show dependency chain
            $this->assertStringContainsString('Dependency chain:', $message);
            $this->assertStringContainsString('DeepMiddle', $message);
            $this->assertStringContainsString('DeepBottom', $message);

            // Should show root cause
            $this->assertStringContainsString('Root cause:', $message);
            $this->assertStringContainsString('NonExistent', $message);

            // Should NOT contain verbose reflection dumps
            $this->assertStringNotContainsString('Method [ <user', $message);
        }
    }

    public function testInterfaceWithoutBindingErrorMessage(): void
    {
        $injector = new Injector(new TopLevel());

        try {
            $injector->get(UnboundInterface::class);
            $this->fail('Should have thrown NotFoundException');
        } catch (NotFoundException $e) {
            $message = $e->getMessage();

            $this->assertStringContainsString('Cannot create', $message);
            $this->assertStringContainsString('UnboundInterface', $message);
            $this->assertStringContainsString('Root cause:', $message);
            $this->assertStringContainsString('not bound', $message);
        }
    }

    public function testUntypedParameterErrorMessage(): void
    {
        $injector = new Injector(new TopLevel());

        try {
            $injector->get(UntypedConstructor::class);
            $this->fail('Should have thrown NotFoundException');
        } catch (NotFoundException $e) {
            $message = $e->getMessage();

            $this->assertStringContainsString('Cannot create', $message);
            $this->assertStringContainsString('UntypedConstructor', $message);

            // Should NOT contain verbose reflection dumps
            $this->assertStringNotContainsString('Method [ <user', $message);
            $this->assertStringNotContainsString('Parameters [', $message);
        }
    }

    public function testSimplifiedParameterErrorMessage(): void
    {
        $injector = new Injector(new TopLevel());

        try {
            $injector->get(UntypedConstructor::class);
            $this->fail('Should have thrown NotFoundException');
        } catch (NotFoundException $e) {
            // Walk exception chain to find DependencyFinder message
            $current = $e;
            $foundSimplified = false;

            while ($current) {
                $msg = $current->getMessage();
                // Check for simplified format: "Cannot resolve parameter $x (type) for Class::method()"
                if (preg_match('/Cannot resolve parameter \$\w+ \(\w+\) for .+::\w+\(\)/', $msg)) {
                    $foundSimplified = true;
                    break;
                }
                $current = $current->getPrevious();
            }

            $this->assertTrue($foundSimplified, 'Should contain simplified parameter error message');
        }
    }

    public function testRootCauseExtractedCorrectly(): void
    {
        $injector = new Injector(new TopLevel());

        try {
            $injector->get(DeepTopLevel::class);
            $this->fail('Should have thrown NotFoundException');
        } catch (NotFoundException $e) {
            $message = $e->getMessage();

            // Root cause should mention the actual missing class
            $this->assertStringContainsString('Root cause:', $message);
            $this->assertStringContainsString('NonExistent', $message);

            // Should be concise, not include full stack trace in message
            $lines = explode("\n", $message);
            $this->assertLessThan(5, count($lines), 'Error message should be concise (< 5 lines)');
        }
    }

    public function testDependencyChainFormattedReadably(): void
    {
        $injector = new Injector(new TopLevel());

        try {
            $injector->get(DeepTopLevel::class);
            $this->fail('Should have thrown NotFoundException');
        } catch (NotFoundException $e) {
            $message = $e->getMessage();

            // Should use arrow notation for chain
            $this->assertStringContainsString('→', $message);

            // Should have all classes in order
            $this->assertMatchesRegularExpression(
                '/DeepTopLevel.*→.*DeepMiddle.*→.*DeepBottom/',
                $message,
                'Dependency chain should be in correct order'
            );
        }
    }
}

// Test fixtures
interface UnboundInterface
{
}

class UntypedConstructor
{
    public function __construct($untyped)
    {
    }
}

class DeepTopLevel
{
    public function __construct(DeepMiddle $m)
    {
    }
}

class DeepMiddle
{
    public function __construct(DeepBottom $b)
    {
    }
}

class DeepBottom
{
    public function __construct(NonExistent $x)
    {
    }
}

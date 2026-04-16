<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit;

use Horde\Injector\DependencyFinder;
use Horde\Injector\Injector;
use Horde\Injector\NotFoundException;
use Horde\Injector\Test\Unit\Fixture\ClassImplementingAnInterface;
use Horde\Injector\Test\Unit\Fixture\ClassWithMultipleOptionalParams;
use Horde\Injector\TopLevel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DependencyFinder::class)]
class DependencyFinderEdgeCaseTest extends TestCase
{
    /**
     * Variadic parameters are skipped during dependency resolution.
     * The class is instantiated without passing any variadic arguments.
     */
    public function testVariadicParameterResolvesToEmptyArray(): void
    {
        $injector = new Injector(new TopLevel());
        $result = $injector->get(VariadicFixture::class);
        $this->assertInstanceOf(VariadicFixture::class, $result);
        $this->assertSame([], $result->items);
    }

    /**
     * Builtin typed optional parameters use their default values.
     */
    public function testBuiltinTypedOptionalUsesDefault(): void
    {
        $injector = new Injector(new TopLevel());
        $result = $injector->get(BuiltinDefaultFixture::class);
        $this->assertInstanceOf(BuiltinDefaultFixture::class, $result);
        $this->assertSame(5, $result->count);
    }

    /**
     * When a parameter can't be resolved, the exception message includes
     * the method context: "Cannot resolve parameter $name (type) for Class::method()"
     */
    public function testExceptionIncludesMethodContext(): void
    {
        $injector = new Injector(new TopLevel());
        try {
            $injector->get(UnresolvableDepFixture::class);
            $this->fail('Expected NotFoundException');
        } catch (NotFoundException $e) {
            // Walk the exception chain to find the context message
            $found = false;
            $current = $e;
            while ($current) {
                $msg = $current->getMessage();
                if (str_contains($msg, 'Cannot resolve parameter')) {
                    $this->assertStringContainsString('$dep', $msg);
                    $this->assertStringContainsString('UnresolvableDepFixture', $msg);
                    $this->assertStringContainsString('__construct', $msg);
                    $found = true;
                    break;
                }
                $current = $current->getPrevious();
            }
            $this->assertTrue($found, 'Exception chain should contain "Cannot resolve parameter" context');
        }
    }

    /**
     * All optional parameters get their default values when no bindings exist.
     */
    public function testMultipleOptionalParamsAllGetDefaults(): void
    {
        $injector = new Injector(new TopLevel());
        $result = $injector->get(ClassWithMultipleOptionalParams::class);
        $this->assertInstanceOf(ClassWithMultipleOptionalParams::class, $result);
        $this->assertSame('x', $result->getA());
        $this->assertSame(0, $result->getB());
        $this->assertNull($result->getC());
    }
}

class VariadicFixture
{
    public array $items;

    public function __construct(string ...$items)
    {
        $this->items = $items;
    }
}

class BuiltinDefaultFixture
{
    public function __construct(
        public readonly int $count = 5,
    ) {}
}

class UnresolvableDepFixture
{
    public function __construct(
        public readonly UnresolvableDepInterface $dep,
    ) {}
}

interface UnresolvableDepInterface {}

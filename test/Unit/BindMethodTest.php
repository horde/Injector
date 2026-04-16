<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit;

use Horde\Injector\Binder\Implementation;
use Horde\Injector\Injector;
use Horde\Injector\Test\Unit\Fixture\AnInterface;
use Horde\Injector\Test\Unit\Fixture\ClassImplementingAnInterface;
use Horde\Injector\TopLevel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Injector::class)]
class BindMethodTest extends TestCase
{
    /**
     * Short binder name (existing behavior via __call magic).
     * bindImplementation('AnInterface', 'ClassImpl') resolves "Implementation"
     * to Horde\Injector\Binder\Implementation.
     */
    public function testBindShortNameResolvesBuiltinBinder(): void
    {
        $injector = new Injector(new TopLevel());
        $injector->bindImplementation(AnInterface::class, ClassImplementingAnInterface::class);
        $this->assertInstanceOf(
            ClassImplementingAnInterface::class,
            $injector->get(AnInterface::class)
        );
    }

    /**
     * FQCN binder name: when the type contains a backslash, bind() uses
     * it as-is instead of prepending the default namespace.
     */
    public function testBindFqcnResolvesBinder(): void
    {
        $injector = new Injector(new TopLevel());
        // Call __call with "bind" + FQCN — strips "bind" prefix, leaving the FQCN
        $fqcn = Implementation::class;
        $methodName = 'bind' . $fqcn;
        $binder = $injector->$methodName(AnInterface::class, ClassImplementingAnInterface::class);
        $this->assertInstanceOf(Implementation::class, $binder);
        $this->assertInstanceOf(
            ClassImplementingAnInterface::class,
            $injector->get(AnInterface::class)
        );
    }
}

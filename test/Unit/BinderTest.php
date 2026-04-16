<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit;

use Horde\Injector\Binder\AnnotatedSetters;
use Horde\Injector\Binder\Closure as ClosureBinder;
use Horde\Injector\Binder\Factory as FactoryBinder;
use Horde\Injector\Binder\Implementation as ImplementationBinder;
use Horde\Injector\DependencyFinder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ImplementationBinder::class)]
#[CoversClass(FactoryBinder::class)]
#[CoversClass(ClosureBinder::class)]
#[CoversClass(AnnotatedSetters::class)]
class BinderTest extends TestCase
{
    /**
     * Provider returns binder1, binder2, shouldEqual, errmesg
     */
    public static function binderIsEqualProvider(): array
    {
        $df = new DependencyFinder();
        return [
            [
                new ImplementationBinder('foobar', $df),
                new FactoryBinder('factory', 'method'),
                false,
                "Implementation_Binder should not equal Factory binder",
            ],
            [
                new ImplementationBinder('foobar', $df),
                new ImplementationBinder('foobar', $df),
                true,
                "Implementation Binders both reference concrete class foobar",
            ],
            [
                new ImplementationBinder('foobar', $df),
                new ImplementationBinder('otherimpl', $df),
                false,
                "Implementation Binders do not have same implementation set",
            ],
            [
                new FactoryBinder('factory', 'method'),
                new ImplementationBinder('foobar', $df),
                false,
                "Implementation_Binder should not equal Factory binder",
            ],
            [
                new FactoryBinder('foobar', 'create'),
                new FactoryBinder('foobar', 'create'),
                true,
                "Factory Binders both reference factory class foobar::create",
            ],
            [
                new FactoryBinder('foobar', 'create'),
                new FactoryBinder('otherimpl', 'create'),
                false,
                "Factory Binders do not have same factory class set, so they should not be equal",
            ],
            [
                new FactoryBinder('foobar', 'create'),
                new FactoryBinder('foobar', 'otherMethod'),
                false,
                "Factory Binders are set to the same class but different methods. They should not be equal",
            ],
            // Closure binder equality
            (function () {
                $closure = function ($injector) { return new \stdClass(); };
                return [
                    new ClosureBinder($closure),
                    new ClosureBinder($closure),
                    true,
                    "Closure Binders referencing the same closure should be equal",
                ];
            })(),
            [
                new ClosureBinder(function ($injector) { return new \stdClass(); }),
                new ClosureBinder(function ($injector) { return new \stdClass(); }),
                false,
                "Closure Binders referencing different closures should not be equal",
            ],
            [
                new ClosureBinder(function ($injector) { return new \stdClass(); }),
                new ImplementationBinder('foobar', $df),
                false,
                "Closure Binder should not equal Implementation Binder",
            ],
            // AnnotatedSetters equality (delegates to inner binder)
            [
                new AnnotatedSetters(new ImplementationBinder('foobar', $df), $df),
                new AnnotatedSetters(new ImplementationBinder('foobar', $df), $df),
                true,
                "AnnotatedSetters wrapping equal Implementation binders should be equal",
            ],
            [
                new AnnotatedSetters(new ImplementationBinder('foobar', $df), $df),
                new AnnotatedSetters(new ImplementationBinder('otherimpl', $df), $df),
                false,
                "AnnotatedSetters wrapping different Implementation binders should not be equal",
            ],
            [
                new AnnotatedSetters(new ImplementationBinder('foobar', $df), $df),
                new ImplementationBinder('foobar', $df),
                false,
                "AnnotatedSetters should not equal bare Implementation Binder",
            ],
        ];
    }

    /**
     * @param mixed $binderA
     * @param mixed $binderB
     * @param bool $shouldEqual
     * @param string $message
     */
    #[DataProvider('binderIsEqualProvider')]
    public function testBinderEqualFunction($binderA, $binderB, $shouldEqual, $message): void
    {
        $this->assertEquals($shouldEqual, $binderA->equals($binderB), $message);
    }
}

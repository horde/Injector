<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit;

use Horde\Injector\Binder\Factory as FactoryBinder;
use Horde\Injector\Binder\Implementation as ImplementationBinder;
use Horde\Injector\DependencyFinder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
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

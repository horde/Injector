<?php

namespace Horde\Injector\Test;

use Horde\Injector\Binder\Factory as FactoryBinder;
use Horde\Injector\Binder\Implementation as ImplementationBinder;
use Horde\Injector\DependencyFinder;

class BinderTest extends \Horde\Test\TestCase
{
    /**
     * provider returns binder1, binder2, shouldEqual, errmesg
     */
    public function binderIsEqualProvider()
    {
        $df = new DependencyFinder();
        return [
            [
                new ImplementationBinder('foobar', $df),
                new FactoryBinder('factory', 'method'),
                false, "Implementation_Binder should not equal Factory binder"
            ],
            [
                new ImplementationBinder('foobar', $df),
                new ImplementationBinder('foobar', $df),
                true, "Implementation Binders both reference concrete class foobar"
            ],
            [
                new ImplementationBinder('foobar', $df),
                new ImplementationBinder('otherimpl', $df),
                false, "Implementation Binders do not have same implementation set"
            ],
            [
                new FactoryBinder('factory', 'method'),
                new ImplementationBinder('foobar', $df),
                false, "Implementation_Binder should not equal Factory binder"
            ],
            [
                new FactoryBinder('foobar', 'create'),
                new FactoryBinder('foobar', 'create'),
                true, "Factory Binders both reference factory class foobar::create"
            ],
            [
                new FactoryBinder('foobar', 'create'),
                new FactoryBinder('otherimpl', 'create'),
                false, "Factory Binders do not have same factory class set, so they should not be equal"
            ],
            [
                new FactoryBinder('foobar', 'create'),
                new FactoryBinder('foobar', 'otherMethod'),
                false, "Factory Binders are set to the same class but different methods. They should not be equal"
            ],
        ];
    }

    /**
     * @dataProvider binderIsEqualProvider
     */
    public function testBinderEqualFunction($binderA, $binderB, $shouldEqual, $message)
    {
        $this->assertEquals($shouldEqual, $binderA->equals($binderB), $message);
    }
}

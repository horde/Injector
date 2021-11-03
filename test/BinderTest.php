<?php
namespace Horde\Injector\Test;
use Horde\Injector\DependencyFinder;
use Horde\Injector\Binder\Implementation as ImplementationBinder;
use Horde\Injector\Binder\Factory as FactoryBinder;

class BinderTest extends \Horde\Test\TestCase
{
    /**
     * provider returns binder1, binder2, shouldEqual, errmesg
     */
    public function binderIsEqualProvider()
    {
        $df = new DependencyFinder();
        return array(
            array(
                new ImplementationBinder('foobar', $df),
                new FactoryBinder('factory', 'method'),
                false, "Implementation_Binder should not equal Factory binder"
            ),
            array(
                new ImplementationBinder('foobar', $df),
                new ImplementationBinder('foobar', $df),
                true, "Implementation Binders both reference concrete class foobar"
            ),
            array(
                new ImplementationBinder('foobar', $df),
                new ImplementationBinder('otherimpl', $df),
                false, "Implementation Binders do not have same implementation set"
            ),
            array(
                new FactoryBinder('factory', 'method'),
                new ImplementationBinder('foobar', $df),
                false, "Implementation_Binder should not equal Factory binder"
            ),
            array(
                new FactoryBinder('foobar', 'create'),
                new FactoryBinder('foobar', 'create'),
                true, "Factory Binders both reference factory class foobar::create"
            ),
            array(
                new FactoryBinder('foobar', 'create'),
                new FactoryBinder('otherimpl', 'create'),
                false, "Factory Binders do not have same factory class set, so they should not be equal"
            ),
            array(
                new FactoryBinder('foobar', 'create'),
                new FactoryBinder('foobar', 'otherMethod'),
                false, "Factory Binders are set to the same class but different methods. They should not be equal"
            ),
        );
    }

    /**
     * @dataProvider binderIsEqualProvider
     */
    public function testBinderEqualFunction($binderA, $binderB, $shouldEqual, $message)
    {
        $this->assertEquals($shouldEqual, $binderA->equals($binderB), $message);
    }
}

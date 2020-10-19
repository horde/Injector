<?php
namespace Horde\Injector;

class BinderTest extends \Horde_Test_Case
{
    /**
     * provider returns binder1, binder2, shouldEqual, errmesg
     */
    public function binderIsEqualProvider()
    {
        $df = new DependencyFinder();
        return array(
            array(
                new Binder\Implementation('foobar', $df),
                new Binder\Factory('factory', 'method'),
                false, "Implementation_Binder should not equal Factory binder"
            ),
            array(
                new Binder\Implementation('foobar', $df),
                new Binder\Implementation('foobar', $df),
                true, "Implementation Binders both reference concrete class foobar"
            ),
            array(
                new Binder\Implementation('foobar', $df),
                new Binder\Implementation('otherimpl', $df),
                false, "Implementation Binders do not have same implementation set"
            ),
            array(
                new Binder\Factory('factory', 'method'),
                new Binder\Implementation('foobar', $df),
                false, "Implementation_Binder should not equal Factory binder"
            ),
            array(
                new Binder\Factory('foobar', 'create'),
                new Binder\Factory('foobar', 'create'),
                true, "Factory Binders both reference factory class foobar::create"
            ),
            array(
                new Binder\Factory('foobar', 'create'),
                new Binder\Factory('otherimpl', 'create'),
                false, "Factory Binders do not have same factory class set, so they should not be equal"
            ),
            array(
                new Binder\Factory('foobar', 'create'),
                new Binder\Factory('foobar', 'otherMethod'),
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

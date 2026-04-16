<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit;

use Horde\Injector\Binder\AnnotatedSetters;
use Horde\Injector\Binder\Implementation;
use Horde\Injector\TopLevel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TopLevel::class)]
class TopLevelTest extends TestCase
{
    public function testGetBinderReturnsAnnotatedSetters(): void
    {
        $topLevel = new TopLevel();
        $binder = $topLevel->getBinder('SomeInterface');
        $this->assertInstanceOf(AnnotatedSetters::class, $binder);
    }

    public function testGetBinderInnerBinderIsImplementation(): void
    {
        $topLevel = new TopLevel();
        $binder = $topLevel->getBinder('SomeInterface');
        $this->assertInstanceOf(AnnotatedSetters::class, $binder);
        $inner = $binder->getBinder();
        $this->assertInstanceOf(Implementation::class, $inner);
        $this->assertSame('SomeInterface', $inner->getImplementation());
    }

    public function testGetInstanceReturnsNull(): void
    {
        $topLevel = new TopLevel();
        $this->assertNull($topLevel->getInstance('Anything'));
    }

    public function testGetReturnsNull(): void
    {
        $topLevel = new TopLevel();
        $this->assertNull($topLevel->get('Anything'));
    }

    public function testHasReturnsFalse(): void
    {
        $topLevel = new TopLevel();
        $this->assertFalse($topLevel->has('Anything'));
        $this->assertFalse($topLevel->has('stdClass'));
        $this->assertFalse($topLevel->has('Horde\Injector\Injector'));
    }
}

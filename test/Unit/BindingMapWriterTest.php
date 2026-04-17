<?php

declare(strict_types=1);

namespace Horde\Injector\Test\Unit;

use Horde\Injector\Binder\AnnotatedSetters;
use Horde\Injector\Binder\Closure as ClosureBinder;
use Horde\Injector\Binder\Factory;
use Horde\Injector\Binder\Implementation;
use Horde\Injector\BindingMapWriter;
use Horde\Injector\Injector;
use Horde\Injector\TopLevel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(BindingMapWriter::class)]
class BindingMapWriterTest extends TestCase
{
    public function testExtractImplementationBinding(): void
    {
        $injector = new Injector(new TopLevel());
        $injector->addBinder('FooInterface', new Implementation(stdClass::class));

        $writer = new BindingMapWriter();
        $result = $writer->extractBindings($injector);

        $this->assertSame(stdClass::class, $result['cacheable']['FooInterface']);
        $this->assertSame([], $result['uncacheable']);
    }

    public function testExtractFactoryBinding(): void
    {
        $injector = new Injector(new TopLevel());
        $injector->addBinder('BarInterface', new Factory('BarFactory', 'create'));

        $writer = new BindingMapWriter();
        $result = $writer->extractBindings($injector);

        $this->assertSame(['BarFactory', 'create'], $result['cacheable']['BarInterface']);
    }

    public function testExtractAnnotatedSettersUnwraps(): void
    {
        $injector = new Injector(new TopLevel());
        $inner = new Implementation(stdClass::class);
        $injector->addBinder('WrappedInterface', new AnnotatedSetters($inner));

        $writer = new BindingMapWriter();
        $result = $writer->extractBindings($injector);

        $this->assertSame(stdClass::class, $result['cacheable']['WrappedInterface']);
    }

    public function testExtractClosureSkipped(): void
    {
        $injector = new Injector(new TopLevel());
        $injector->addBinder('ClosureInterface', new ClosureBinder(fn($i) => new stdClass()));

        $writer = new BindingMapWriter();
        $result = $writer->extractBindings($injector);

        $this->assertArrayNotHasKey('ClosureInterface', $result['cacheable']);
        $this->assertSame(['ClosureInterface'], $result['uncacheable']);
    }

    public function testWriteProducesValidPhp(): void
    {
        $tmpFile = sys_get_temp_dir() . '/injector_bindings_test_' . uniqid() . '.php';

        try {
            $bindings = [
                'FooInterface' => 'ConcreteFoo',
                'BarInterface' => ['BarFactory', 'create'],
            ];

            $writer = new BindingMapWriter();
            $writer->write($tmpFile, $bindings);

            $this->assertFileExists($tmpFile);
            $loaded = require $tmpFile;
            $this->assertSame($bindings, $loaded);
        } finally {
            @unlink($tmpFile);
        }
    }

    public function testRoundTrip(): void
    {
        $tmpFile = sys_get_temp_dir() . '/injector_roundtrip_test_' . uniqid() . '.php';

        try {
            // Build injector with mixed bindings
            $injector = new Injector(new TopLevel());
            $injector->addBinder(WriterRoundTripInterface::class, new Implementation(WriterRoundTripConcrete::class));
            $injector->addBinder('factory.key', new Factory(WriterRoundTripFactory::class, 'create'));
            $injector->addBinder('closure.key', new ClosureBinder(fn($i) => new stdClass()));

            // Extract and write
            $writer = new BindingMapWriter();
            $result = $writer->extractBindings($injector);
            $writer->write($tmpFile, $result['cacheable']);

            // Load into fresh injector
            $fresh = new Injector(new TopLevel(), require $tmpFile);

            // Implementation binding resolves
            $this->assertInstanceOf(
                WriterRoundTripConcrete::class,
                $fresh->get(WriterRoundTripInterface::class)
            );

            // Factory binding resolves
            $this->assertInstanceOf(
                WriterRoundTripConcrete::class,
                $fresh->get('factory.key')
            );

            // Closure was excluded
            $this->assertSame(['closure.key'], $result['uncacheable']);
        } finally {
            @unlink($tmpFile);
        }
    }
}

interface WriterRoundTripInterface {}

class WriterRoundTripConcrete implements WriterRoundTripInterface {}

class WriterRoundTripFactory
{
    public function create(Injector $injector): WriterRoundTripConcrete
    {
        return new WriterRoundTripConcrete();
    }
}

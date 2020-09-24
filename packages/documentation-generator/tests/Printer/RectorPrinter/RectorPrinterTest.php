<?php

declare(strict_types=1);

namespace Rector\DocumentationGenerator\Tests\Printer\RectorPrinter;

use Iterator;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\DocumentationGenerator\Printer\RectorPrinter;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use ReflectionClass;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

final class RectorPrinterTest extends AbstractKernelTestCase
{
    /**
     * @var RectorPrinter
     */
    private $rectorPrinter;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);
        $this->rectorPrinter = self::$container->get(RectorPrinter::class);
    }

    /**
     * @dataProvider provideData()
     */
    public function test(string $rectorClass, string $expectedContentFilePath): void
    {
        $reflectionClass = new ReflectionClass($rectorClass);

        /** @var RectorInterface $rector */
        $rector = $reflectionClass->newInstanceWithoutConstructor();

        $printedContent = $this->rectorPrinter->printRector($rector, false);
        $this->assertStringEqualsFile($expectedContentFilePath, $printedContent);
    }

    public function provideData(): Iterator
    {
        yield [TypedPropertyRector::class, __DIR__ . '/Fixture/expected_typed_property_rector.txt'];
    }
}

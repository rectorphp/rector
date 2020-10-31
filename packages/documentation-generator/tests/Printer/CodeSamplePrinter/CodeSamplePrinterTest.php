<?php

declare(strict_types=1);

namespace Rector\DocumentationGenerator\Tests\Printer\CodeSamplePrinter;

use Iterator;
use Rector\Autodiscovery\Rector\FileNode\MoveValueObjectsToValueObjectDirectoryRector;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\DocumentationGenerator\Printer\CodeSamplePrinter;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use ReflectionClass;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class CodeSamplePrinterTest extends AbstractKernelTestCase
{
    /**
     * @var CodeSamplePrinter
     */
    private $codeSamplePrinter;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);
        $this->codeSamplePrinter = self::$container->get(CodeSamplePrinter::class);
    }

    /**
     * @dataProvider provideData()
     */
    public function test(string $rectorClass, string $expectedPrintedCodeSampleFilePath): void
    {
        $reflectionClass = new ReflectionClass($rectorClass);
        $rector = $reflectionClass->newInstanceWithoutConstructor();

        /** @var RectorInterface $rector */
        $this->assertInstanceOf(RectorInterface::class, $rector);

        $printedCodeSamples = $this->codeSamplePrinter->printCodeSamples($rector->getDefinition(), $rector);

        $expectedFileInfo = new SmartFileInfo($expectedPrintedCodeSampleFilePath);
        $this->assertStringEqualsFile(
            $expectedPrintedCodeSampleFilePath,
            $printedCodeSamples,
            $expectedFileInfo->getRelativeFilePathFromCwd()
        );
    }

    public function provideData(): Iterator
    {
        yield [TypedPropertyRector::class, __DIR__ . '/Fixture/expected_typed_property_code_sample.txt'];
        yield [
            MoveValueObjectsToValueObjectDirectoryRector::class,
            __DIR__ . '/Fixture/expected_value_object_rector.txt',
        ];
    }
}

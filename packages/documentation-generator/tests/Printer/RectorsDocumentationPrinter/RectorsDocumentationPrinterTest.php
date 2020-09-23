<?php

declare(strict_types=1);

namespace Rector\DocumentationGenerator\Tests\Printer\RectorsDocumentationPrinter;

use Iterator;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\DocumentationGenerator\Printer\RectorsDocumentationPrinter;
use Rector\Generic\Rector\PropertyFetch\RenamePropertyRector;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use ReflectionClass;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

final class RectorsDocumentationPrinterTest extends AbstractKernelTestCase
{
    /**
     * @var RectorsDocumentationPrinter
     */
    private $rectorsDocumentationPrinter;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);
        $this->rectorsDocumentationPrinter = self::$container->get(RectorsDocumentationPrinter::class);
    }

    /**
     * @dataProvider provideData()
     */
    public function test(array $rectorClasses, bool $isRectorProject, string $expectedContentFilePath): void
    {
        $rectors = $this->createRectorsFromRectorClasses($rectorClasses);

        $printedContent = $this->rectorsDocumentationPrinter->print($rectors, $isRectorProject);

        $this->assertStringEqualsFile($expectedContentFilePath, $printedContent);
    }

    public function provideData(): Iterator
    {
        yield [
            [TypedPropertyRector::class, RenamePropertyRector::class],
            false,
            __DIR__ . '/Fixture/expected_rectors.txt',
        ];
    }

    private function createRectorsFromRectorClasses(array $rectorClasses): array
    {
        $rectors = [];

        foreach ($rectorClasses as $rectorClass) {
            $reflectionClass = new ReflectionClass($rectorClass);
            $rectors[] = $reflectionClass->newInstanceWithoutConstructor();
        }

        return $rectors;
    }
}

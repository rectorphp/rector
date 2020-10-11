<?php

declare(strict_types=1);

namespace Rector\DocumentationGenerator\Tests\Printer\RectorsDocumentationPrinter;

use Iterator;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\DocumentationGenerator\Printer\RectorsDocumentationPrinter;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector;
use ReflectionClass;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

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
     * @param string[] $rectorClasses
     * @dataProvider provideData()
     */
    public function test(array $rectorClasses, bool $isRectorProject, string $expectedContentFilePath): void
    {
        $rectors = $this->createRectorsFromRectorClasses($rectorClasses);

        $printedContent = $this->rectorsDocumentationPrinter->print($rectors, $isRectorProject);
        $expectedFileInfo = new SmartFileInfo($expectedContentFilePath);

        $this->assertStringEqualsFile(
            $expectedContentFilePath,
            $printedContent,
            $expectedFileInfo->getRelativeFilePathFromCwd()
        );
    }

    public function provideData(): Iterator
    {
        yield [
            [TypedPropertyRector::class, RenamePropertyRector::class],
            false,
            __DIR__ . '/Fixture/expected_rectors.txt',
        ];
    }

    /**
     * @param string[] $rectorClasses
     * @return RectorInterface[]
     */
    private function createRectorsFromRectorClasses(array $rectorClasses): array
    {
        $rectors = [];

        foreach ($rectorClasses as $rectorClass) {
            $reflectionClass = new ReflectionClass($rectorClass);
            $rector = $reflectionClass->newInstanceWithoutConstructor();
            if (! $rector instanceof RectorInterface) {
                throw new ShouldNotHappenException();
            }

            $rectors[] = $rector;
        }

        return $rectors;
    }
}

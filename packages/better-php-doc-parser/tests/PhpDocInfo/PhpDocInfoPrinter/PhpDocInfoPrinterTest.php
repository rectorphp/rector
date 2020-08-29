<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocInfo\PhpDocInfoPrinter;

use Iterator;
use PhpParser\Node\Stmt\Nop;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PhpDocInfoPrinterTest extends AbstractPhpDocInfoPrinterTest
{
    /**
     * @dataProvider provideData()
     * @dataProvider provideDataCallable()
     */
    public function test(SmartFileInfo $docFileInfo): void
    {
        $this->doComparePrintedFileEquals($docFileInfo, $docFileInfo);
    }

    public function testRemoveSpace(): void
    {
        $this->doComparePrintedFileEquals(
            new SmartFileInfo(__DIR__ . '/FixtureChanged/with_space.txt'),
            new SmartFileInfo(__DIR__ . '/FixtureChangedExpected/with_space_expected.txt')
        );
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureBasic', '*.txt');
    }

    public function provideDataCallable(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureCallable', '*.txt');
    }

    /**
     * @dataProvider provideDataEmpty()
     */
    public function testEmpty(SmartFileInfo $fileInfo): void
    {
        $phpDocInfo = $this->createPhpDocInfoFromDocCommentAndNode($fileInfo->getContents(), new Nop());

        $this->assertEmpty($this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo));
    }

    public function provideDataEmpty(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureEmpty', '*.txt');
    }

    private function doComparePrintedFileEquals(SmartFileInfo $inputFileInfo, SmartFileInfo $expectedFileInfo): void
    {
        $phpDocInfo = $this->createPhpDocInfoFromDocCommentAndNode($inputFileInfo->getContents(), new Nop());

        $printedDocComment = $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo);

        $this->assertSame(
            $expectedFileInfo->getContents(),
            $printedDocComment,
            $inputFileInfo->getRelativeFilePathFromCwd()
        );
    }
}

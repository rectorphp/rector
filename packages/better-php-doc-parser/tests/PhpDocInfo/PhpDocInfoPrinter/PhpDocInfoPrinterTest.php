<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocInfo\PhpDocInfoPrinter;

use Iterator;
use Nette\Utils\FileSystem;
use PhpParser\Node\Stmt\Nop;

final class PhpDocInfoPrinterTest extends AbstractPhpDocInfoPrinterTest
{
    /**
     * @dataProvider provideData()
     * @dataProvider provideDataCallable()
     */
    public function test(string $docFilePath): void
    {
        $docComment = FileSystem::read($docFilePath);
        $phpDocInfo = $this->createPhpDocInfoFromDocCommentAndNode($docComment, new Nop());

        $this->assertSame($docComment, $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo));
    }

    public function testRemoveSpace(): void
    {
        $docComment = FileSystem::read(__DIR__ . '/FixtureChanged/with_space.txt');
        $phpDocInfo = $this->createPhpDocInfoFromDocCommentAndNode($docComment, new Nop());

        $expectedDocComment = FileSystem::read(__DIR__ . '/FixtureChanged/with_space_expected.txt.inc');

        $printedDocComment = $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo);

        $this->assertSame($expectedDocComment, $printedDocComment);
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
    public function testEmpty(string $docFilePath): void
    {
        $docComment = FileSystem::read($docFilePath);
        $phpDocInfo = $this->createPhpDocInfoFromDocCommentAndNode($docComment, new Nop());

        $this->assertEmpty($this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo));
    }

    public function provideDataEmpty(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureEmpty', '*.txt');
    }
}

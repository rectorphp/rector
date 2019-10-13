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
     */
    public function test(string $docFilePath): void
    {
        $docComment = FileSystem::read($docFilePath);
        $phpDocInfo = $this->createPhpDocInfoFromDocCommentAndNode($docComment, new Nop());

        $this->assertSame($docComment, $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo));
    }

    public function provideData(): Iterator
    {
        yield [__DIR__ . '/Source/Basic/doc.txt'];
        yield [__DIR__ . '/Source/Basic/doc2.txt'];
        yield [__DIR__ . '/Source/Basic/doc3.txt'];
        yield [__DIR__ . '/Source/Basic/doc4.txt'];
        yield [__DIR__ . '/Source/Basic/doc5.txt'];
        yield [__DIR__ . '/Source/Basic/doc6.txt'];
        yield [__DIR__ . '/Source/Basic/doc7.txt'];
        yield [__DIR__ . '/Source/Basic/doc8.txt'];
        yield [__DIR__ . '/Source/Basic/doc9.txt'];
        yield [__DIR__ . '/Source/Basic/doc10.txt'];
        yield [__DIR__ . '/Source/Basic/doc11.txt'];
        yield [__DIR__ . '/Source/Basic/doc13.txt'];
        yield [__DIR__ . '/Source/Basic/doc14.txt'];
        yield [__DIR__ . '/Source/Basic/doc15.txt'];
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
        yield [__DIR__ . '/Source/Basic/empty-doc.txt'];
    }
}

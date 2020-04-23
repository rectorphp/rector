<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocInfo\PhpDocInfoPrinter;

use Iterator;
use Nette\Utils\FileSystem;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\BetterPhpDocParser\Tests\PhpDocInfo\PhpDocInfoPrinter\Source\Doctrine\CaseSensitive;
use Rector\BetterPhpDocParser\Tests\PhpDocInfo\PhpDocInfoPrinter\Source\Doctrine\IndexInTable;
use Rector\BetterPhpDocParser\Tests\PhpDocInfo\PhpDocInfoPrinter\Source\Doctrine\Short;
use Symplify\SmartFileSystem\SmartFileInfo;

final class DoctrineTest extends AbstractPhpDocInfoPrinterTest
{
    /**
     * @dataProvider provideDataClass()
     */
    public function testClass(string $docFilePath, Node $node): void
    {
        $docComment = FileSystem::read($docFilePath);
        $phpDocInfo = $this->createPhpDocInfoFromDocCommentAndNode($docComment, $node);

        $fileInfo = new SmartFileInfo($docFilePath);
        $message = $fileInfo->getRelativeFilePathFromCwd();

        $this->assertSame($docComment, $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo), $message);
    }

    public function provideDataClass(): Iterator
    {
        yield [__DIR__ . '/Source/Doctrine/index_in_table.txt', new Class_(IndexInTable::class)];
        yield [__DIR__ . '/Source/Doctrine/case_sensitive.txt', new Class_(CaseSensitive::class)];
        yield [__DIR__ . '/Source/Doctrine/short.txt', new Class_(Short::class)];
    }
}

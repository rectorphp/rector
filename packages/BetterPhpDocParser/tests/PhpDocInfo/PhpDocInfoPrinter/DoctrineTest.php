<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocInfo\PhpDocInfoPrinter;

use Iterator;
use Nette\Utils\FileSystem;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\BetterPhpDocParser\Tests\PhpDocInfo\PhpDocInfoPrinter\Source\Doctrine\IndexInTable;

final class DoctrineTest extends AbstractPhpDocInfoPrinterTest
{
    /**
     * @dataProvider provideDataForTestClass()
     */
    public function testClass(string $docFilePath, Node $node, string $expectedDocFilePath): void
    {
        $docComment = FileSystem::read($docFilePath);
        $phpDocInfo = $this->createPhpDocInfoFromDocCommentAndNode($docComment, $node);

        $expectedDoc = FileSystem::read($expectedDocFilePath);

        $this->assertSame(
            $expectedDoc,
            $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo),
            'Caused in ' . $docFilePath
        );
    }

    /**
     * @return string[]|Class_[]
     */
    public function provideDataForTestClass(): Iterator
    {
        yield [
            __DIR__ . '/Source/Doctrine/index_in_table.txt',
            new Class_(IndexInTable::class),
            __DIR__ . '/Source/Doctrine/expected_index_in_table.txt',
        ];
    }
}

<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser;

use Iterator;
use PhpParser\Node;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter;
use Rector\BetterPhpDocParser\Tests\PhpDocParser\Helper\TagValueToPhpParserNodeMap;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\FileSystemRector\Parser\FileInfoParser;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

abstract class AbstractPhpDocInfoTest extends AbstractKernelTestCase
{
    /**
     * @var FileInfoParser
     */
    private $fileInfoParser;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var PhpDocInfoPrinter
     */
    private $phpDocInfoPrinter;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);

        $this->fileInfoParser = self::$container->get(FileInfoParser::class);

        $this->betterNodeFinder = self::$container->get(BetterNodeFinder::class);
        $this->phpDocInfoPrinter = self::$container->get(PhpDocInfoPrinter::class);
    }

    /**
     * @param class-string $tagValueNodeType
     */
    protected function doTestPrintedPhpDocInfo(SmartFileInfo $fileInfo, string $tagValueNodeType): void
    {
        if (! isset(TagValueToPhpParserNodeMap::MAP[$tagValueNodeType])) {
            throw new ShouldNotHappenException(sprintf(
                '[tests] Add "%s" to %s::%s constant',
                $tagValueNodeType,
                TagValueToPhpParserNodeMap::class,
                'MAP'
            ));
        }

        $nodeType = TagValueToPhpParserNodeMap::MAP[$tagValueNodeType];

        $nodeWithPhpDocInfo = $this->parseFileAndGetFirstNodeOfType($fileInfo, $nodeType);

        $docComment = $nodeWithPhpDocInfo->getDocComment();
        if ($docComment === null) {
            throw new ShouldNotHappenException(sprintf('Doc comments for "%s" file cannot not be empty', $fileInfo));
        }

        $originalDocCommentText = $docComment->getText();
        $printedPhpDocInfo = $this->printNodePhpDocInfoToString($nodeWithPhpDocInfo);

        $errorMessage = $this->createErrorMessage($fileInfo);
        $this->assertSame($originalDocCommentText, $printedPhpDocInfo, $errorMessage);

        $this->doTestContainsTagValueNodeType($nodeWithPhpDocInfo, $tagValueNodeType, $fileInfo);
    }

    protected function yieldFilesFromDirectory(string $directory, string $suffix = '*.php'): Iterator
    {
        return StaticFixtureFinder::yieldDirectory($directory, $suffix);
    }

    protected function findFilesFromDirectory(string $directory, string $suffix = '*.php'): Iterator
    {
        return StaticFixtureFinder::yieldDirectory($directory, $suffix);
    }

    /**
     * @param class-string $nodeType
     */
    private function parseFileAndGetFirstNodeOfType(SmartFileInfo $fileInfo, string $nodeType): Node
    {
        $nodes = $this->fileInfoParser->parseFileInfoToNodesAndDecorate($fileInfo);

        return $this->betterNodeFinder->findFirstInstanceOf($nodes, $nodeType);
    }

    private function printNodePhpDocInfoToString(Node $node): string
    {
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            throw new ShouldNotHappenException();
        }

        return $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo);
    }

    private function createErrorMessage(SmartFileInfo $fileInfo): string
    {
        return 'Caused by: ' . $fileInfo->getRelativeFilePathFromCwd() . PHP_EOL;
    }

    private function doTestContainsTagValueNodeType(Node $node, string $tagValueNodeType, SmartFileInfo $fileInfo): void
    {
        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);

        $this->assertTrue($phpDocInfo->hasByType($tagValueNodeType), $fileInfo->getRelativeFilePathFromCwd());
    }
}

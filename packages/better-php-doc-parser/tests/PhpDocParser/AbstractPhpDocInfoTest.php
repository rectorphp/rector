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
use Rector\Core\Testing\StaticFixtureProvider;
use Rector\FileSystemRector\Parser\FileInfoParser;
use Rector\NodeTypeResolver\Node\AttributeKey;
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
    protected function doTestPrintedPhpDocInfo(string $filePath, string $tagValueNodeType): void
    {
        $nodeType = TagValueToPhpParserNodeMap::MAP[$tagValueNodeType];

        $nodeWithPhpDocInfo = $this->parseFileAndGetFirstNodeOfType($filePath, $nodeType);

        $docComment = $nodeWithPhpDocInfo->getDocComment();
        if ($docComment === null) {
            throw new ShouldNotHappenException(sprintf('Doc comments for "%s" file cannot not be empty', $filePath));
        }

        $originalDocCommentText = $docComment->getText();
        $printedPhpDocInfo = $this->printNodePhpDocInfoToString($nodeWithPhpDocInfo);

        $errorMessage = $this->createErrorMessage($filePath);
        $this->assertSame($originalDocCommentText, $printedPhpDocInfo, $errorMessage);

        $this->doTestContainsTagValueNodeType($nodeWithPhpDocInfo, $tagValueNodeType);
    }

    protected function yieldFilesFromDirectory(string $directory, string $suffix = '*.php'): Iterator
    {
        return StaticFixtureProvider::yieldFilesFromDirectory($directory, $suffix);
    }

    /**
     * @return string[]
     */
    protected function findFilesFromDirectory(string $directory, string $suffix = '*.php'): array
    {
        return StaticFixtureProvider::findFilesFromDirectory($directory, $suffix);
    }

    /**
     * @param class-string $nodeType
     */
    private function parseFileAndGetFirstNodeOfType(string $filePath, string $nodeType): Node
    {
        $nodes = $this->fileInfoParser->parseFileInfoToNodesAndDecorate(new SmartFileInfo($filePath));
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

    private function createErrorMessage(string $filePath): string
    {
        $fileInfo = new SmartFileInfo($filePath);

        return 'Caused by: ' . $fileInfo->getRelativeFilePathFromCwd() . PHP_EOL;
    }

    private function doTestContainsTagValueNodeType(Node $node, string $tagValueNodeType): void
    {
        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        $this->assertTrue($phpDocInfo->hasByType($tagValueNodeType));
    }
}

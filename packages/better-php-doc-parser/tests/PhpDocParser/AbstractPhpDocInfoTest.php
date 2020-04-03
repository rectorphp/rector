<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser;

use Iterator;
use PhpParser\Node;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter;
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
     * @param class-string $nodeType
     */
    protected function doTestPrintedPhpDocInfo(string $filePath, string $nodeType, string $tagValueNodeType): void
    {
        $this->ensureIsNodeType($nodeType);

        $nodeWithPhpDocInfo = $this->parseFileAndGetFirstNodeOfType($filePath, $nodeType);

        $docComment = $nodeWithPhpDocInfo->getDocComment();
        if ($docComment === null) {
            throw new ShouldNotHappenException(sprintf('Doc comments for "%s" file cannot not be empty', $filePath));
        }

        $originalDocCommentText = $docComment->getText();
        $printedPhpDocInfo = $this->printNodePhpDocInfoToString($nodeWithPhpDocInfo);

        $this->assertSame($originalDocCommentText, $printedPhpDocInfo);

        $this->doTestContainsTagValueNodeType($nodeWithPhpDocInfo, $tagValueNodeType);
    }

    protected function yieldFilesFromDirectory(string $directory, string $suffix = '*.php'): Iterator
    {
        return StaticFixtureProvider::yieldFilesFromDirectory($directory, $suffix);
    }

    private function doTestContainsTagValueNodeType(Node $node, string $tagValueNodeType): void
    {
        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        $phpDocInfo->hasByType($tagValueNodeType);
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

    /**
     * @param class-string $nodeType
     */
    private function ensureIsNodeType(string $nodeType): void
    {
        if (is_a($nodeType, Node::class, true)) {
            return;
        }

        throw new ShouldNotHappenException(sprintf('"%s" must be type of "%s"', $nodeType, Node::class));
    }
}

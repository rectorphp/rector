<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\OrmTagParser;

use PhpParser\Node;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter;
use Rector\FileSystemRector\Parser\FileInfoParser;
use Rector\HttpKernel\RectorKernel;
use Rector\PhpParser\Node\BetterNodeFinder;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

abstract class AbstractPhpDocInfoTest extends AbstractKernelTestCase
{
    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

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

        $this->phpDocInfoFactory = self::$container->get(PhpDocInfoFactory::class);
        $this->fileInfoParser = self::$container->get(FileInfoParser::class);

        $this->betterNodeFinder = self::$container->get(BetterNodeFinder::class);
        $this->phpDocInfoPrinter = self::$container->get(PhpDocInfoPrinter::class);
    }

    protected function parseFileAndGetFirstNodeOfType(string $filePath, string $type): Node
    {
        $nodes = $this->fileInfoParser->parseFileInfoToNodesAndDecorate(new SmartFileInfo($filePath));

        return $this->betterNodeFinder->findFirstInstanceOf($nodes, $type);
    }

    protected function createPhpDocInfoFromNodeAndPrintBackToString(Node $node): string
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);

        return $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo);
    }
}

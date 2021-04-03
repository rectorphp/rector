<?php

declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint;

use Iterator;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\FileSystemRector\Parser\FileInfoParser;
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\EasyTesting\FixtureSplitter\TrioFixtureSplitter;
use Symplify\EasyTesting\ValueObject\FixtureSplit\TrioContent;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class TagValueNodeReprintTest extends AbstractKernelTestCase
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

    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);

        $this->fileInfoParser = $this->getService(FileInfoParser::class);

        $this->betterNodeFinder = $this->getService(BetterNodeFinder::class);
        $this->phpDocInfoPrinter = $this->getService(PhpDocInfoPrinter::class);
        $this->phpDocInfoFactory = $this->getService(PhpDocInfoFactory::class);
    }

    /**
     * @dataProvider provideData()
     * @dataProvider provideDataNested()
     */
    public function test(SmartFileInfo $fixtureFileInfo): void
    {
        $trioFixtureSplitter = new TrioFixtureSplitter();
        $trioContent = $trioFixtureSplitter->splitFileInfo($fixtureFileInfo);

        $nodeClass = trim($trioContent->getSecondValue());
        $tagValueNodeClasses = $this->splitListByEOL($trioContent->getExpectedResult());

        $fixtureFileInfo = $this->createFixtureFileInfo($trioContent, $fixtureFileInfo);
        foreach ($tagValueNodeClasses as $tagValueNodeClass) {
            $this->doTestPrintedPhpDocInfo($fixtureFileInfo, $tagValueNodeClass, $nodeClass);
        }
    }

    /**
     * @return Iterator<SmartFileInfo>
     */
    public function provideData(): Iterator
    {
        return StaticFixtureFinder::yieldDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @return Iterator<SmartFileInfo>
     */
    public function provideDataNested(): Iterator
    {
        return StaticFixtureFinder::yieldDirectory(__DIR__ . '/FixtureNested');
    }

    /**
     * @param class-string $annotationClass
     * @param class-string<Node> $nodeClass
     */
    private function doTestPrintedPhpDocInfo(
        SmartFileInfo $smartFileInfo,
        string $annotationClass,
        string $nodeClass
    ): void {
        $nodeWithPhpDocInfo = $this->parseFileAndGetFirstNodeOfType($smartFileInfo, $nodeClass);

        $docComment = $nodeWithPhpDocInfo->getDocComment();
        if (! $docComment instanceof Doc) {
            throw new ShouldNotHappenException(sprintf(
                'Doc comments for "%s" file cannot not be empty',
                $smartFileInfo
            ));
        }

        $originalDocCommentText = $docComment->getText();
        $printedPhpDocInfo = $this->printNodePhpDocInfoToString($nodeWithPhpDocInfo);

        $this->assertSame($originalDocCommentText, $printedPhpDocInfo);
        $this->doTestContainsTagValueNodeType($nodeWithPhpDocInfo, $annotationClass, $smartFileInfo);
    }

    /**
     * @return string[]
     */
    private function splitListByEOL(string $content): array
    {
        $trimmedContent = trim($content);
        return explode(PHP_EOL, $trimmedContent);
    }

    private function createFixtureFileInfo(TrioContent $trioContent, SmartFileInfo $fixturefileInfo): SmartFileInfo
    {
        $temporaryFileName = sys_get_temp_dir() . '/rector/tests/' . $fixturefileInfo->getRelativePathname();
        $firstValue = $trioContent->getFirstValue();

        $smartFileSystem = new SmartFileSystem();
        $smartFileSystem->dumpFile($temporaryFileName, $firstValue);

        return new SmartFileInfo($temporaryFileName);
    }

    /**
     * @template T as Node
     * @param class-string<T> $nodeType
     * @return T
     */
    private function parseFileAndGetFirstNodeOfType(SmartFileInfo $smartFileInfo, string $nodeType): Node
    {
        $nodes = $this->fileInfoParser->parseFileInfoToNodesAndDecorate($smartFileInfo);

        $node = $this->betterNodeFinder->findFirstInstanceOf($nodes, $nodeType);
        if (! $node instanceof Node) {
            throw new ShouldNotHappenException($smartFileInfo->getRealPath());
        }

        return $node;
    }

    private function printNodePhpDocInfoToString(Node $node): string
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $phpDocInfo->markAsChanged();

        return $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo);
    }

    /**
     * @param class-string $annotationClass
     */
    private function doTestContainsTagValueNodeType(
        Node $node,
        string $annotationClass,
        SmartFileInfo $smartFileInfo
    ): void {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $hasByAnnotationClass = $phpDocInfo->hasByAnnotationClass($annotationClass);

        $this->assertTrue($hasByAnnotationClass, $smartFileInfo->getRelativeFilePathFromCwd());
    }
}

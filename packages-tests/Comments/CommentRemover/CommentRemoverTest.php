<?php

declare(strict_types=1);

namespace Rector\Comments\Tests\CommentRemover;

use Iterator;
use Rector\Comments\CommentRemover;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\FileSystemRector\Parser\FileInfoParser;
use Rector\Testing\PHPUnit\AbstractTestCase;
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\EasyTesting\StaticFixtureSplitter;
use Symplify\SmartFileSystem\SmartFileInfo;

final class CommentRemoverTest extends AbstractTestCase
{
    private CommentRemover $commentRemover;

    private FileInfoParser $fileInfoParser;

    private BetterStandardPrinter $betterStandardPrinter;

    protected function setUp(): void
    {
        $this->boot();
        $this->commentRemover = $this->getService(CommentRemover::class);
        $this->fileInfoParser = $this->getService(FileInfoParser::class);
        $this->betterStandardPrinter = $this->getService(BetterStandardPrinter::class);
    }

    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $smartFileInfo): void
    {
        $fileInfoToLocalInputAndExpected = StaticFixtureSplitter::splitFileInfoToLocalInputAndExpected($smartFileInfo);

        $nodes = $this->fileInfoParser->parseFileInfoToNodesAndDecorate(
            $fileInfoToLocalInputAndExpected->getInputFileInfo()
        );

        $nodesWithoutComments = $this->commentRemover->removeFromNode($nodes);

        $fileContent = $this->betterStandardPrinter->print($nodesWithoutComments);
        $fileContent = trim($fileContent);

        $expectedContent = trim($fileInfoToLocalInputAndExpected->getExpected());

        $this->assertSame($fileContent, $expectedContent, $smartFileInfo->getRelativeFilePathFromCwd());

        // original nodes are not touched
        $originalContent = $this->betterStandardPrinter->print($nodes);
        $this->assertNotSame($expectedContent, $originalContent);
    }

    public function provideData(): Iterator
    {
        return StaticFixtureFinder::yieldDirectory(__DIR__ . '/Fixture', '*.php.inc');
    }
}

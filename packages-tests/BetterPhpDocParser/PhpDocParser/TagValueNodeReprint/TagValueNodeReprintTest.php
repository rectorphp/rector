<?php

declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint;

use Iterator;
use Rector\Tests\BetterPhpDocParser\PhpDocParser\AbstractPhpDocInfoTest;
use Symplify\EasyTesting\FixtureSplitter\TrioFixtureSplitter;
use Symplify\EasyTesting\ValueObject\FixtureSplit\TrioContent;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class TagValueNodeReprintTest extends AbstractPhpDocInfoTest
{
    /**
     * @dataProvider provideData()
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
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture', '*.php.inc');
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

        // to make it doctrine/annotation parse-able
        require_once $temporaryFileName;

        return new SmartFileInfo($temporaryFileName);
    }
}

<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\EasyTesting\Tests\StaticFixtureSplitter;

use RectorPrefix20210510\PHPUnit\Framework\TestCase;
use RectorPrefix20210510\Symplify\EasyTesting\StaticFixtureSplitter;
use Symplify\SmartFileSystem\SmartFileInfo;
final class StaticFixtureSplitterTest extends TestCase
{
    public function test() : void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Source/simple_fixture.php.inc');
        $inputAndExpected = StaticFixtureSplitter::splitFileInfoToInputAndExpected($fileInfo);
        $this->assertSame('a' . \PHP_EOL, $inputAndExpected->getInput());
        $this->assertSame('b' . \PHP_EOL, $inputAndExpected->getExpected());
    }
    public function testSplitFileInfoToLocalInputAndExpected() : void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Source/file_and_value.php.inc');
        $inputFileInfoAndExpected = StaticFixtureSplitter::splitFileInfoToLocalInputAndExpected($fileInfo);
        $inputFileRealPath = $inputFileInfoAndExpected->getInputFileRealPath();
        $this->assertFileExists($inputFileRealPath);
        $this->assertSame(15025, $inputFileInfoAndExpected->getExpected());
    }
}

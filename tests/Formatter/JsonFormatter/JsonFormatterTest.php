<?php
declare(strict_types=1);

namespace Rector\Core\Tests\Formatter\JsonFormatter;

use Iterator;
use Rector\Core\Formatter\JsonFormatter;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObjectFactory\EditorConfigConfigurationBuilder;
use Rector\Testing\PHPUnit\AbstractTestCase;
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\EasyTesting\StaticFixtureSplitter;
use Symplify\SmartFileSystem\SmartFileInfo;

final class JsonFormatterTest extends AbstractTestCase
{
    /**
     * @var JsonFormatter
     */
    private $jsonFormatter;

    protected function setUp(): void
    {
        $this->boot();
        $this->jsonFormatter = $this->getService(JsonFormatter::class);
    }

    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return StaticFixtureFinder::yieldDirectory(__DIR__ . '/Fixture', '*.json');
    }

    private function doTestFileInfo(SmartFileInfo $smartFileInfo): void
    {
        $inputFileInfoAndExpected = StaticFixtureSplitter::splitFileInfoToLocalInputAndExpected($smartFileInfo);

        $inputFileInfo = $inputFileInfoAndExpected->getInputFileInfo();
        $file = new File($inputFileInfo, $inputFileInfo->getContents());

        $editorConfigConfigurationBuilder = EditorConfigConfigurationBuilder::anEditorConfigConfiguration();
        $editorConfigConfigurationBuilder->withTab();
        $editorConfigConfigurationBuilder->withTabWidth(1);
        $editorConfigConfigurationBuilder->withFinalNewline();
        $editorConfigConfigurationBuilder->withLineFeed();

        $this->jsonFormatter->format($file, $editorConfigConfigurationBuilder->build());

        $this->assertSame($inputFileInfoAndExpected->getExpected(), $file->getFileContent());
    }
}

<?php

declare(strict_types=1);

namespace Rector\Tests\FileFormatter\Formatter\JsonFileFormatter;

use Iterator;
use Rector\Core\ValueObject\Application\File;
use Rector\FileFormatter\Formatter\JsonFileFormatter;
use Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder;
use Rector\Testing\PHPUnit\AbstractTestCase;
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\EasyTesting\StaticFixtureSplitter;
use Symplify\SmartFileSystem\SmartFileInfo;

final class JsonFileFormatterTest extends AbstractTestCase
{
    /**
     * @var JsonFileFormatter
     */
    private $jsonFormatter;

    protected function setUp(): void
    {
        $this->boot();
        $this->jsonFormatter = $this->getService(JsonFileFormatter::class);
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

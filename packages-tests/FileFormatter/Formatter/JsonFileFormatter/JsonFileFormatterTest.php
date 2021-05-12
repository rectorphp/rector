<?php

declare(strict_types=1);

namespace Rector\Tests\FileFormatter\Formatter\JsonFileFormatter;

use Iterator;
use Rector\Core\ValueObject\Application\File;
use Rector\FileFormatter\Formatter\JsonFileFormatter;
use Rector\FileFormatter\ValueObject\Indent;
use Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder;
use Rector\Testing\PHPUnit\AbstractTestCase;
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\EasyTesting\StaticFixtureSplitter;
use Symplify\SmartFileSystem\SmartFileInfo;

final class JsonFileFormatterTest extends AbstractTestCase
{
    private JsonFileFormatter $jsonFileFormatter;

    protected function setUp(): void
    {
        $this->boot();
        $this->jsonFileFormatter = $this->getService(JsonFileFormatter::class);
    }

    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    /**
     * @return Iterator<array<int, SmartFileInfo>>
     */
    public function provideData(): Iterator
    {
        return StaticFixtureFinder::yieldDirectory(__DIR__ . '/Fixture', '*.json');
    }

    private function doTestFileInfo(SmartFileInfo $smartFileInfo): void
    {
        $inputFileInfoAndExpected = StaticFixtureSplitter::splitFileInfoToLocalInputAndExpected($smartFileInfo);

        $inputFileInfo = $inputFileInfoAndExpected->getInputFileInfo();
        $file = new File($inputFileInfo, $inputFileInfo->getContents());

        $editorConfigConfigurationBuilder = new EditorConfigConfigurationBuilder();
        $editorConfigConfigurationBuilder->withIndent(Indent::createTabWithSize(1));

        $this->jsonFileFormatter->format($file, $editorConfigConfigurationBuilder->build());

        $this->assertSame($inputFileInfoAndExpected->getExpected(), $file->getFileContent());
    }
}

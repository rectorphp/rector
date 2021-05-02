<?php

declare(strict_types=1);

namespace Rector\Tests\FileFormatter\Formatter\NeonFileFormatter;

use Iterator;
use Rector\Core\ValueObject\Application\File;
use Rector\FileFormatter\Formatter\NeonFileFormatter;
use Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder;
use Rector\Testing\PHPUnit\AbstractTestCase;
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\EasyTesting\StaticFixtureSplitter;
use Symplify\SmartFileSystem\SmartFileInfo;

final class NeonFileFormatterTest extends AbstractTestCase
{
    /**
     * @var NeonFileFormatter
     */
    private $neonFormatter;

    protected function setUp(): void
    {
        $this->boot();
        $this->neonFormatter = $this->getService(NeonFileFormatter::class);
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
        return StaticFixtureFinder::yieldDirectory(__DIR__ . '/Fixture', '*.neon');
    }

    private function doTestFileInfo(SmartFileInfo $smartFileInfo): void
    {
        $inputFileInfoAndExpected = StaticFixtureSplitter::splitFileInfoToLocalInputAndExpected($smartFileInfo);

        $inputFileInfo = $inputFileInfoAndExpected->getInputFileInfo();
        $file = new File($inputFileInfo, $inputFileInfo->getContents());

        $editorConfigConfigurationBuilder = EditorConfigConfigurationBuilder::anEditorConfigConfiguration();
        $editorConfigConfigurationBuilder->withTab();
        $editorConfigConfigurationBuilder->withTabWidth(8);
        $editorConfigConfigurationBuilder->withFinalNewline();
        $editorConfigConfigurationBuilder->withLineFeed();

        $this->neonFormatter->format($file, $editorConfigConfigurationBuilder->build());

        $this->assertSame($inputFileInfoAndExpected->getExpected(), $file->getFileContent());
    }
}

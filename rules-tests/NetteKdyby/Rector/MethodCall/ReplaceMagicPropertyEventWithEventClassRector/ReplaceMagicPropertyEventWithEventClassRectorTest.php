<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector;

use Iterator;
use Rector\NetteKdyby\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ReplaceMagicPropertyEventWithEventClassRectorTest extends AbstractRectorTestCase
{
    public function testSkip(): void
    {
        $fixtureFileInfo = new SmartFileInfo(__DIR__ . '/Fixture/skip_on_success_in_control.php.inc');
        $this->doTestFileInfo($fixtureFileInfo);
    }

    /**
     * @dataProvider provideData()
     */
    public function test(
        SmartFileInfo $fixtureFileInfo,
        string $expectedRelativeFilePath,
        string $expectedContentFilePath
    ): void {
        $this->doTestFileInfo($fixtureFileInfo);

        $this->doTestExtraFile($expectedRelativeFilePath, $expectedContentFilePath);
    }

    public function provideData(): Iterator
    {
        yield [
            new SmartFileInfo(__DIR__ . '/Fixture/simple_event.php.inc'),
            '/Event/FileManagerUploadEvent.php',
            __DIR__ . '/Source/ExpectedFileManagerUploadEvent.php',
        ];

        yield [
            new SmartFileInfo(__DIR__ . '/Fixture/duplicated_event_params.php.inc'),
            '/Event/DuplicatedEventParamsUploadEvent.php',
            __DIR__ . '/Source/ExpectedDuplicatedEventParamsUploadEvent.php',
        ];
    }

    protected function getRectorClass(): string
    {
        return ReplaceMagicPropertyEventWithEventClassRector::class;
    }
}

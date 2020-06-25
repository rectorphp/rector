<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector;

use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\NetteKdyby\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ReplaceMagicPropertyEventWithEventClassRectorTest extends AbstractRectorTestCase
{
    public function testSkip(): void
    {
        $fixtureFileInfo = new SmartFileInfo(__DIR__ . '/Fixture/skip_on_success_in_control.php.inc');
        $this->doTestFileInfo($fixtureFileInfo);
    }

    public function testSimpleEvent(): void
    {
        $fixtureFileInfo = new SmartFileInfo(__DIR__ . '/Fixture/simple_event.php.inc');
        $this->doTestFileInfo($fixtureFileInfo);

        $expectedEventFilePath = $this->originalTempFileInfo->getPath() . '/Event/FileManagerUploadEvent.php';
        $this->assertFileExists($expectedEventFilePath);
        $this->assertFileEquals(__DIR__ . '/Source/ExpectedFileManagerUploadEvent.php', $expectedEventFilePath);
    }

    public function testDuplicatedEventParams(): void
    {
        $fixtureFileInfo = new SmartFileInfo(__DIR__ . '/Fixture/duplicated_event_params.php.inc');
        $this->doTestFileInfo($fixtureFileInfo);

        $expectedEventFilePath = $this->originalTempFileInfo->getPath() . '/Event/DuplicatedEventParamsUploadEvent.php';
        $this->assertFileExists($expectedEventFilePath);
        $this->assertFileEquals(
            __DIR__ . '/Source/ExpectedDuplicatedEventParamsUploadEvent.php',
            $expectedEventFilePath
        );
    }

    protected function getRectorClass(): string
    {
        return ReplaceMagicPropertyEventWithEventClassRector::class;
    }
}

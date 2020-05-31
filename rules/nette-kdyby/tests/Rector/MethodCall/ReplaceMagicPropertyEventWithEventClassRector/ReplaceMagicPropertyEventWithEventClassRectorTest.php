<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector;

use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\NetteKdyby\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector;

final class ReplaceMagicPropertyEventWithEventClassRectorTest extends AbstractRectorTestCase
{
    public function testSkip(): void
    {
        $this->doTestFile(__DIR__ . '/Fixture/skip_on_success_in_control.php.inc');
    }

    public function testSimpleEvent(): void
    {
        $this->doTestFile(__DIR__ . '/Fixture/simple_event.php.inc');

        $expectedEventFilePath = $this->originalTempFileInfo->getPath() . '/Event/FileManagerUploadEvent.php';
        $this->assertFileExists($expectedEventFilePath);
        $this->assertFileEquals(__DIR__ . '/Source/ExpectedFileManagerUploadEvent.php', $expectedEventFilePath);
    }

    public function testDuplicatedEventParams(): void
    {
        $this->doTestFile(__DIR__ . '/Fixture/duplicated_event_params.php.inc');

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

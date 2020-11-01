<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector;

use Iterator;
use Rector\Core\ValueObject\PhpVersionFeature;
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

        $expectedEventFilePath = $this->originalTempFileInfo->getPath() . $expectedRelativeFilePath;
        $this->assertFileExists($expectedEventFilePath);

        $this->assertFileEquals($expectedContentFilePath, $expectedEventFilePath);
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

    protected function getPhpVersion(): string
    {
        return PhpVersionFeature::BEFORE_TYPED_PROPERTIES;
    }

    protected function getRectorClass(): string
    {
        return ReplaceMagicPropertyEventWithEventClassRector::class;
    }
}

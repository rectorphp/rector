<?php

declare(strict_types=1);

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ChangeSwitchToMatchRectorSkippedTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    /**
     * @return Iterator<SmartFileInfo>
     */
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureSkipped');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/configured_rule_php_version_74.php';
    }
}

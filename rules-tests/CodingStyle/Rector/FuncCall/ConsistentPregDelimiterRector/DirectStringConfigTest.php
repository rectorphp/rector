<?php

declare(strict_types=1);

namespace Rector\Tests\CodingStyle\Rector\FuncCall\ConsistentPregDelimiterRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class DirectStringConfigTest extends AbstractRectorTestCase
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
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureDirectStringConfig');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/direct_string_configured_rule.php';
    }
}

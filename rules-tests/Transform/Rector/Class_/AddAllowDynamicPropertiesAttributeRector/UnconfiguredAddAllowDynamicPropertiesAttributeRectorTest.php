<?php

declare(strict_types=1);

namespace Rector\Tests\Transform\Rector\Class_\AddAllowDynamicPropertiesAttributeRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class UnconfiguredAddAllowDynamicPropertiesAttributeRectorTest extends AbstractRectorTestCase
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
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureAllClasses');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/unconfigured_rule.php';
    }
}

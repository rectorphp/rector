<?php

declare(strict_types=1);

namespace Rector\Tests\Symfony3\Rector\MethodCall\StringFormTypeToClassRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class WithContainerTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    /**
     * @return Iterator<mixed, SmartFileInfo>
     */
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureWithContainer');
    }

    protected function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/xml_path_config.php';
    }
}

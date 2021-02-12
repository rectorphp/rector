<?php

declare(strict_types=1);

namespace Rector\Symfony3\Tests\Rector\MethodCall\StringFormTypeToClassRector;

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

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureWithContainer');
    }

    protected function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/xml_path_config.php';
    }
}

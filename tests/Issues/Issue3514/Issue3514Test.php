<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\Issue3514;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class Issue3514Test extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $filePath): void
    {
        $this->doTestFileInfo($filePath);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config3514.yaml';
    }
}

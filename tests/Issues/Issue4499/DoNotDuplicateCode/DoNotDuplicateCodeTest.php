<?php
declare(strict_types=1);

namespace Rector\Core\Tests\Issues\Issue4499\DoNotDuplicateCode;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class DoNotDuplicateCodeTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        @$this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    // bin/rector process ... --config config/some_config.php

    protected function provideConfigFileInfo(): SmartFileInfo
    {
        return new SmartFileInfo(__DIR__ . '/config/some_config.php');
    }
}

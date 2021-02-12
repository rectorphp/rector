<?php

declare(strict_types=1);

namespace Rector\ModeratePackage\Tests\Rector\MethodCall\WhateverRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class WhateverRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): \Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function provideConfigFilePath(): string
    {
        return __DIR__ . '/configured_rule.php';
    }
}

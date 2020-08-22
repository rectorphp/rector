<?php

declare(strict_types=1);

namespace Rector\Naming\Tests\Rector\ClassMethod\MakeIsserClassMethodNameStartWithIsRector;

use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;

final class MakeIsserClassMethodNameStartWithIsRectorTest extends AbstractRectorTestCase
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

    protected function getRectorClass(): string
    {
        return \Rector\Naming\Rector\ClassMethod\MakeIsserClassMethodNameStartWithIsRector::class;
    }
}

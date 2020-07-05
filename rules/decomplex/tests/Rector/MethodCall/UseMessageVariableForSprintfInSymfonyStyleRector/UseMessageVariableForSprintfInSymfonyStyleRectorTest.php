<?php

declare(strict_types=1);

namespace Rector\Decomplex\Tests\Rector\MethodCall\UseMessageVariableForSprintfInSymfonyStyleRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Decomplex\Rector\MethodCall\UseMessageVariableForSprintfInSymfonyStyleRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class UseMessageVariableForSprintfInSymfonyStyleRectorTest extends AbstractRectorTestCase
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
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return UseMessageVariableForSprintfInSymfonyStyleRector::class;
    }
}

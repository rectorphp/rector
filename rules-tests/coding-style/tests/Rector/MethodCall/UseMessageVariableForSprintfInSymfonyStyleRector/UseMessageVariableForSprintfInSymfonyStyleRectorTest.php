<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\MethodCall\UseMessageVariableForSprintfInSymfonyStyleRector;

use Iterator;
use Rector\CodingStyle\Rector\MethodCall\UseMessageVariableForSprintfInSymfonyStyleRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
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

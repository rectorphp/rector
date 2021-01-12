<?php

declare(strict_types=1);

namespace Rector\Privatization\Tests\Rector\MethodCall\ReplaceStringWithClassConstantRector;

use Iterator;
use Rector\Privatization\Rector\MethodCall\ReplaceStringWithClassConstantRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ReplaceStringWithClassConstantRectorTest extends AbstractRectorTestCase
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

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ReplaceStringWithClassConstantRector::class =>
                [
                    ReplaceStringWithClassConstantRector::methodCallWithPositionAndClassConstants => [
                        'before' => 'after',
                    ],
                ],
        ];
    }
}

<?php

declare(strict_types=1);

namespace Rector\Transform\Tests\Rector\StaticCall\StaticCallToNewRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Transform\Tests\Rector\StaticCall\StaticCallToNewRector\Source\SomeJsonResponse;

final class StaticCallToNewRectorTest extends AbstractRectorTestCase
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

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            \Rector\Transform\Rector\StaticCall\StaticCallToNewRector::class =>
                [
                    \Rector\Transform\Rector\StaticCall\StaticCallToNewRector::STATIC_CALLS_TO_NEWS => [
                        SomeJsonResponse::class => 'create',
                    ],
                ],
        ];
    }
}

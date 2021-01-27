<?php

declare(strict_types=1);

namespace Rector\Transform\Tests\Rector\StaticCall\StaticCallToNewRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Transform\Rector\StaticCall\StaticCallToNewRector;
use Rector\Transform\Tests\Rector\StaticCall\StaticCallToNewRector\Source\SomeJsonResponse;
use Rector\Transform\ValueObject\StaticCallToNew;
use Symplify\SmartFileSystem\SmartFileInfo;

final class StaticCallToNewRectorTest extends AbstractRectorTestCase
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
            StaticCallToNewRector::class =>
                [
                    StaticCallToNewRector::STATIC_CALLS_TO_NEWS => [
                        new StaticCallToNew(SomeJsonResponse::class, 'create'),
                    ],
                ],
        ];
    }
}

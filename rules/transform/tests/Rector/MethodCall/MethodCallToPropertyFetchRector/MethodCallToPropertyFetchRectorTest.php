<?php

declare(strict_types=1);

namespace Rector\Transform\Tests\Rector\MethodCall\MethodCallToPropertyFetchRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Transform\Rector\MethodCall\MethodCallToPropertyFetchRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MethodCallToPropertyFetchRectorTest extends AbstractRectorTestCase
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
            MethodCallToPropertyFetchRector::class => [
                MethodCallToPropertyFetchRector::METHOD_CALL_TO_PROPERTY_FETCHES => [
                    'getEntityManager' => 'entityManager',
                ],
            ],
        ];
    }
}

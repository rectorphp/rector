<?php

declare(strict_types=1);

namespace Rector\Transform\Tests\Rector\Assign\DimFetchAssignToMethodCallRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Transform\Rector\Assign\DimFetchAssignToMethodCallRector;
use Rector\Transform\ValueObject\DimFetchAssignToMethodCall;
use Symplify\SmartFileSystem\SmartFileInfo;

final class DimFetchAssignToMethodCallRectorTest extends AbstractRectorTestCase
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
     * @return array<string, array<string, DimFetchAssignToMethodCall[]>>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            DimFetchAssignToMethodCallRector::class => [
                DimFetchAssignToMethodCallRector::DIM_FETCH_ASSIGN_TO_METHOD_CALL => [
                    new DimFetchAssignToMethodCall(
                        'Nette\Application\Routers\RouteList',
                        'Nette\Application\Routers\Route',
                        'addRoute'
                    ),
                ],
            ],
        ];
    }
}

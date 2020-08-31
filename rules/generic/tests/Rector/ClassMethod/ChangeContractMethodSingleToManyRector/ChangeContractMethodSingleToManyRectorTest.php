<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\ClassMethod\ChangeContractMethodSingleToManyRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\ClassMethod\ChangeContractMethodSingleToManyRector;
use Rector\Generic\Tests\Rector\ClassMethod\ChangeContractMethodSingleToManyRector\Source\OneToManyInterface;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ChangeContractMethodSingleToManyRectorTest extends AbstractRectorTestCase
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
     * @return array<string, array<string, array<string, array<string, string>>>>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ChangeContractMethodSingleToManyRector::class => [
                ChangeContractMethodSingleToManyRector::OLD_TO_NEW_METHOD_BY_TYPE => [
                    OneToManyInterface::class => [
                        'getNode' => 'getNodes',
                    ],
                ],
            ],
        ];
    }
}

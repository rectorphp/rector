<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\ClassMethod\ChangeContractMethodSingleToManyRector;

use Iterator;
use Rector\Core\Rector\ClassMethod\ChangeContractMethodSingleToManyRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\Tests\Rector\ClassMethod\ChangeContractMethodSingleToManyRector\Source\OneToManyInterface;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ChangeContractMethodSingleToManyRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $file): void
    {
        $this->doTestFileInfo($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorsWithConfiguration(): array
    {
        return [
            ChangeContractMethodSingleToManyRector::class => [
                '$oldToNewMethodByType' => [
                    OneToManyInterface::class => [
                        'getNode' => 'getNodes',
                    ],
                ],
            ],
        ];
    }
}

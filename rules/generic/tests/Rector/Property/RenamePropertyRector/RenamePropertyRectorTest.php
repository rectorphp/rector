<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\Property\RenamePropertyRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\Property\RenamePropertyRector;
use Rector\Generic\Tests\Rector\Property\RenamePropertyRector\Source\ClassWithProperties;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RenamePropertyRectorTest extends AbstractRectorTestCase
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
            RenamePropertyRector::class => [
                '$oldToNewPropertyByTypes' => [
                    ClassWithProperties::class => [
                        'oldProperty' => 'newProperty',
                        'anotherOldProperty' => 'anotherNewProperty',
                    ],
                ],
            ],
        ];
    }
}

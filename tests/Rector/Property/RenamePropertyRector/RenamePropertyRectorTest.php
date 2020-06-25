<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\Property\RenamePropertyRector;

use Iterator;
use Rector\Core\Rector\Property\RenamePropertyRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\Tests\Rector\Property\RenamePropertyRector\Source\ClassWithProperties;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RenamePropertyRectorTest extends AbstractRectorTestCase
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

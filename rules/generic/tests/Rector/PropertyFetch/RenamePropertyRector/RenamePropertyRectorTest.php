<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\PropertyFetch\RenamePropertyRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\PropertyFetch\RenamePropertyRector;
use Rector\Generic\Tests\Rector\PropertyFetch\RenamePropertyRector\Source\ClassWithProperties;
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
                RenamePropertyRector::OLD_TO_NEW_PROPERTY_BY_TYPES => [
                    ClassWithProperties::class => [
                        'oldProperty' => 'newProperty',
                        'anotherOldProperty' => 'anotherNewProperty',
                    ],
                ],
            ],
        ];
    }
}

<?php

declare(strict_types=1);

namespace Rector\Renaming\Tests\Rector\PropertyFetch\RenamePropertyRector;

use Iterator;
use Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector;
use Rector\Renaming\Tests\Rector\PropertyFetch\RenamePropertyRector\Source\ClassWithProperties;
use Rector\Renaming\ValueObject\RenameProperty;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
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
     * @return array<string, mixed[]>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            RenamePropertyRector::class => [
                RenamePropertyRector::RENAMED_PROPERTIES => [
                    new RenameProperty(ClassWithProperties::class, 'oldProperty', 'newProperty'),
                    new RenameProperty(ClassWithProperties::class, 'anotherOldProperty', 'anotherNewProperty'),
                ],
            ],
        ];
    }
}

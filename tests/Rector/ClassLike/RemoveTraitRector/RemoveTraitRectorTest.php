<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\ClassLike\RemoveTraitRector;

use Iterator;
use Rector\Core\Rector\ClassLike\RemoveTraitRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\Tests\Rector\ClassLike\RemoveTraitRector\Source\TraitToBeRemoved;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveTraitRectorTest extends AbstractRectorTestCase
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
            RemoveTraitRector::class => [
                '$traitsToRemove' => [TraitToBeRemoved::class],
            ],
        ];
    }
}

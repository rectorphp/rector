<?php

declare(strict_types=1);

namespace Rector\Tests\Rector\ClassLike\RemoveTraitRector;

use Iterator;
use Rector\Rector\ClassLike\RemoveTraitRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\ClassLike\RemoveTraitRector\Source\TraitToBeRemoved;

final class RemoveTraitRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
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

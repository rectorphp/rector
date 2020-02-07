<?php

declare(strict_types=1);

namespace Rector\Sylius\Tests\Rector\Review;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Sylius\Rector\Review\ReplaceCreateMethodWithoutReviewerRector;

final class ReplaceCreateMethodWithoutReviewerRectorTest extends AbstractRectorTestCase
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

    protected function getRectorClass(): string
    {
        return ReplaceCreateMethodWithoutReviewerRector::class;
    }
}

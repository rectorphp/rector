<?php

declare(strict_types=1);

namespace Rector\Sylius\Tests\Rector\Review;

use Iterator;
use Rector\Sylius\Rector\Review\ReplaceCreateMethodWithoutReviewerRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ReplaceCreateMethodWithoutReviewerRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/fixture2.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return ReplaceCreateMethodWithoutReviewerRector::class;
    }
}

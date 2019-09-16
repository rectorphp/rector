<?php declare(strict_types=1);

namespace Rector\Sylius\Tests\Rector\Review;

use Rector\Sylius\Rector\Review\ReplaceCreateMethodWithoutReviewerRector;
use Rector\Sylius\Tests\Rector\Review\Source\ReviewFactoryInterface;
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

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/fixture2.php.inc'];
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ReplaceCreateMethodWithoutReviewerRector::class => [
                '$reviewFactoryInterface' => ReviewFactoryInterface::class,
            ],
        ];
    }
}

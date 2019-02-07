<?php declare(strict_types=1);

namespace Rector\Sylius\Tests\Rector\Review;

use Rector\Sylius\Rector\Review\ReplaceCreateMethodWithoutReviewerRector;
use Rector\Sylius\Tests\Rector\Review\Source\ReviewFactoryInterface;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ReplaceCreateMethodWithoutReviewerRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/fixture2.php.inc']);
    }

    public function getRectorClass(): string
    {
        return ReplaceCreateMethodWithoutReviewerRector::class;
    }

    /**
     * @return mixed[]|null
     */
    protected function getRectorConfiguration(): ?array
    {
        return [
            '$reviewFactoryInterface' => ReviewFactoryInterface::class,
        ];
    }
}

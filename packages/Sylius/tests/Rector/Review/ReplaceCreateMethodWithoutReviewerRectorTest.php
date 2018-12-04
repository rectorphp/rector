<?php declare(strict_types=1);

namespace Rector\Sylius\Tests\Rector\Review;

use Rector\Sylius\Rector\Review\ReplaceCreateMethodWithoutReviewerRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ReplaceCreateMethodWithoutReviewerRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Wrong/wrong.php.inc', __DIR__ . '/Wrong/wrong2.php.inc']);
    }

    public function getRectorClass(): string
    {
        return ReplaceCreateMethodWithoutReviewerRector::class;
    }
}

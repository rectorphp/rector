<?php declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\GetMockRector;

use Rector\PHPUnit\Rector\GetMockRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class GetMockRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([[__DIR__ . '/Wrong/wrong.php.inc', __DIR__ . '/Correct/correct.php.inc']]);
    }

    public function getRectorClass(): string
    {
        return GetMockRector::class;
    }
}

<?php declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\SpecificMethod\AssertNotOperatorRector;

use Rector\PHPUnit\Rector\SpecificMethod\AssertNotOperatorRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AssertNotOperatorRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([[__DIR__ . '/Wrong/wrong.php.inc', __DIR__ . '/Correct/correct.php.inc']]);
    }

    public function getRectorClass(): string
    {
        return AssertNotOperatorRector::class;
    }
}

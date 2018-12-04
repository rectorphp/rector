<?php declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\SpecificMethod\AssertComparisonToSpecificMethodRector;

use Rector\PHPUnit\Rector\SpecificMethod\AssertComparisonToSpecificMethodRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AssertComparisonToSpecificMethodRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Wrong/wrong.php.inc']);
    }

    public function getRectorClass(): string
    {
        return AssertComparisonToSpecificMethodRector::class;
    }
}

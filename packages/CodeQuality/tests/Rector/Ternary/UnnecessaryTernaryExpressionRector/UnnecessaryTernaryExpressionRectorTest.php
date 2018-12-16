<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Ternary\UnnecessaryTernaryExpressionRector;

use Rector\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class UnnecessaryTernaryExpressionRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/fixture2.php.inc',
            __DIR__ . '/Fixture/fixture3.php.inc',
            __DIR__ . '/Fixture/fixture4.php.inc',
        ]);
    }

    public function getRectorClass(): string
    {
        return UnnecessaryTernaryExpressionRector::class;
    }
}

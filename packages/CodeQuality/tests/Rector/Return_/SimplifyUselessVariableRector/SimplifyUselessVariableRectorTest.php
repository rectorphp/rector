<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Return_\SimplifyUselessVariableRector;

use Rector\CodeQuality\Rector\Return_\SimplifyUselessVariableRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * Tests copied from:
 * - https://github.com/slevomat/coding-standard/blob/9978172758e90bc2355573e0b5d99062d87b14a3/tests/Sniffs/Variables/data/uselessVariableErrors.fixed.php
 * - https://github.com/slevomat/coding-standard/blob/9978172758e90bc2355573e0b5d99062d87b14a3/tests/Sniffs/Variables/data/uselessVariableNoErrors.php
 */
final class SimplifyUselessVariableRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/fixture2.php.inc',
            __DIR__ . '/Fixture/fixture3.php.inc',
            __DIR__ . '/Fixture/fixture4.php.inc',
            __DIR__ . '/Fixture/keep_visual.php.inc',
        ]);
    }

    public function getRectorClass(): string
    {
        return SimplifyUselessVariableRector::class;
    }
}

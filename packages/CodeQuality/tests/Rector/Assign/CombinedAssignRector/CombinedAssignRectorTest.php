<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Assign\CombinedAssignRector;

use Rector\CodeQuality\Rector\Assign\CombinedAssignRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * Some tests used from:
 * - https://github.com/doctrine/coding-standard/pull/83/files
 * - https://github.com/slevomat/coding-standard/blob/master/tests/Sniffs/Operators/data/requireCombinedAssignmentOperatorErrors.php
 */
final class CombinedAssignRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Wrong/wrong.php.inc', __DIR__ . '/Wrong/wrong2.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return CombinedAssignRector::class;
    }
}

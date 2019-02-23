<?php declare(strict_types=1);

namespace Rector\Celebrity\Tests\Rector\BooleanOp\LogicalToBooleanRector;

use Rector\Celebrity\Rector\BooleanOp\LogicalToBooleanRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class LogicalToBooleanRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/or.php.inc', __DIR__ . '/Fixture/and.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return LogicalToBooleanRector::class;
    }
}

<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector;

use Rector\Php\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class BinaryOpBetweenNumberAndStringRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return BinaryOpBetweenNumberAndStringRector::class;
    }
}

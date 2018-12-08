<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\BinaryOp\SimplifyDeMorganBinaryRector;

use Rector\CodeQuality\Rector\BinaryOp\SimplifyDeMorganBinaryRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SimplifyDeMorganBinaryRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return SimplifyDeMorganBinaryRector::class;
    }
}

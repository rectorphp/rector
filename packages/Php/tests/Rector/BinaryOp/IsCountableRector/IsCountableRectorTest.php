<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\BinaryOp\IsCountableRector;

use Rector\Php\Rector\BinaryOp\IsCountableRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class IsCountableRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    public function getRectorClass(): string
    {
        return IsCountableRector::class;
    }
}

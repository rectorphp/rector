<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\BinaryOp\IsIterableRector;

use Rector\Php\Rector\BinaryOp\IsIterableRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class IsIterableRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Wrong/wrong.php.inc']);
    }

    public function getRectorClass(): string
    {
        return IsIterableRector::class;
    }
}

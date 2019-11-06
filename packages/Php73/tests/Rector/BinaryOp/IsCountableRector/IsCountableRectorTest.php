<?php

declare(strict_types=1);

namespace Rector\Php73\Tests\Rector\BinaryOp\IsCountableRector;

use Rector\Php73\Rector\BinaryOp\IsCountableRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class IsCountableRectorTest extends AbstractRectorTestCase
{
    /**
     * @requires PHP >= 7.3
     */
    public function testPhp73AndHigher(): void
    {
        $this->doTestFile(__DIR__ . '/Fixture/fixture73.php.inc');
    }

    protected function getRectorClass(): string
    {
        return IsCountableRector::class;
    }
}

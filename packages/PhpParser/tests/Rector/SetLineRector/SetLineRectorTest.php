<?php declare(strict_types=1);

namespace Rector\PhpParser\Tests\Rector\SetLineRector;

use Rector\PhpParser\Rector\SetLineRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SetLineRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([[__DIR__ . '/Wrong/wrong.php.inc', __DIR__ . '/Correct/correct.php.inc']]);
    }

    public function getRectorClass(): string
    {
        return SetLineRector::class;
    }
}

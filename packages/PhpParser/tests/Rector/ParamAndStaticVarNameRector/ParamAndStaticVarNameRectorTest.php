<?php declare(strict_types=1);

namespace Rector\PhpParser\Tests\Rector\ParamAndStaticVarNameRector;

use Rector\PhpParser\Rector\ParamAndStaticVarNameRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ParamAndStaticVarNameRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([[__DIR__ . '/Wrong/wrong.php.inc', __DIR__ . '/Correct/correct.php.inc']]);
    }

    public function getRectorClass(): string
    {
        return ParamAndStaticVarNameRector::class;
    }
}

<?php declare(strict_types=1);

namespace Rector\Silverstripe\Tests\Rector\DefineConstantToStaticCallRector;

use Rector\Silverstripe\Rector\DefineConstantToStaticCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @see \Rector\Silverstripe\Rector\DefineConstantToStaticCallRector
 */
final class DefineConstantToStaticCallRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([[__DIR__ . '/Wrong/wrong.php.inc', __DIR__ . '/Correct/correct.php.inc']]);
    }

    public function getRectorClass(): string
    {
        return DefineConstantToStaticCallRector::class;
    }
}

<?php declare(strict_types=1);

namespace Rector\Silverstripe\Tests\Rector\ConstantToStaticCallRector;

use Rector\Silverstripe\Rector\ConstantToStaticCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @see \Rector\Silverstripe\Rector\ConstantToStaticCallRector
 */
final class ConstantToStaticCallRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([[__DIR__ . '/Wrong/wrong.php.inc', __DIR__ . '/Correct/correct.php.inc']]);
    }

    public function getRectorClass(): string
    {
        return ConstantToStaticCallRector::class;
    }
}

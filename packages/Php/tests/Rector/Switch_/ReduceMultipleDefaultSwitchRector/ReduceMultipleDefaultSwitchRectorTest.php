<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\Switch_\ReduceMultipleDefaultSwitchRector;

use Rector\Php\Rector\Switch_\ReduceMultipleDefaultSwitchRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ReduceMultipleDefaultSwitchRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFilesWithoutAutoload([
            __DIR__ . '/Fixture/fixture.php.inc',
            // @see https://github.com/franzliedke/wp-mpdf/commit/9dc489215fbd1adcb514810653a73dea71db8e99#diff-2f1f4a51a2dd3a73ca034a48a67a2320L1373
            __DIR__ . '/Fixture/hidden_in_middle.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return ReduceMultipleDefaultSwitchRector::class;
    }
}

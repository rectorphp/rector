<?php declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Assign\SplitDoubleAssignRector;

use Rector\CodingStyle\Rector\Assign\SplitDoubleAssignRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SplitDoubleAssignRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return SplitDoubleAssignRector::class;
    }
}

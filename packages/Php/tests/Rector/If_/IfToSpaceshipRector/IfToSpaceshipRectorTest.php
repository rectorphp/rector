<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\If_\IfToSpaceshipRector;

use Rector\Php\Rector\If_\IfToSpaceshipRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class IfToSpaceshipRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/skip.php.inc',
            __DIR__ . '/Fixture/complex.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return IfToSpaceshipRector::class;
    }
}

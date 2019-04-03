<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Identical\BooleanNotIdenticalToNotIdenticalRector;

use Rector\CodeQuality\Rector\Identical\BooleanNotIdenticalToNotIdenticalRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class BooleanNotIdenticalToNotIdenticalRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/just_first.php.inc',
            __DIR__ . '/Fixture/keep.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return BooleanNotIdenticalToNotIdenticalRector::class;
    }
}

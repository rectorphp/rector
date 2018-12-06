<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Identical\SimplifyConditionsRector;

use Rector\CodeQuality\Rector\Identical\SimplifyConditionsRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SimplifyConditionsRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/fixture2.php.inc',
            __DIR__ . '/Fixture/fixture3.php.inc',
        ]);
    }

    public function getRectorClass(): string
    {
        return SimplifyConditionsRector::class;
    }
}

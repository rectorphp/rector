<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\EregToPregMatchRector;

use Rector\Php\Rector\FuncCall\EregToPregMatchRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class EregToPregMatchRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/fixture2.php.inc',
            __DIR__ . '/Fixture/fixture3.php.inc',
            __DIR__ . '/Fixture/fixture4.php.inc',
        ]);
    }

    public function getRectorClass(): string
    {
        return EregToPregMatchRector::class;
    }
}

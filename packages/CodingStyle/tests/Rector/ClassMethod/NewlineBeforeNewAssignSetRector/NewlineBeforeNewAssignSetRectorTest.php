<?php declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\NewlineBeforeNewAssignSetRector;

use Rector\CodingStyle\Rector\ClassMethod\NewlineBeforeNewAssignSetRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class NewlineBeforeNewAssignSetRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/skip_false.php.inc',
            __DIR__ . '/Fixture/skip_already_nop.php.inc',
            __DIR__ . '/Fixture/skip_just_one.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return NewlineBeforeNewAssignSetRector::class;
    }
}

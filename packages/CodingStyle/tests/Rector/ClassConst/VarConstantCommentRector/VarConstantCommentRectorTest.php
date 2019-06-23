<?php declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector;

use Rector\CodingStyle\Rector\ClassConst\VarConstantCommentRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class VarConstantCommentRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/correct_invalid.php.inc',
            __DIR__ . '/Fixture/arrays.php.inc',
            __DIR__ . '/Fixture/misc_type.php.inc',
            __DIR__ . '/Fixture/no_slash.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return VarConstantCommentRector::class;
    }
}

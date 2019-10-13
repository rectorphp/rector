<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector;

use Iterator;
use Rector\CodingStyle\Rector\ClassConst\VarConstantCommentRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class VarConstantCommentRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/correct_invalid.php.inc'];
        yield [__DIR__ . '/Fixture/arrays.php.inc'];
        yield [__DIR__ . '/Fixture/misc_type.php.inc'];
        yield [__DIR__ . '/Fixture/no_slash.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return VarConstantCommentRector::class;
    }
}

<?php declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\NewlineBeforeNewAssignSetRector;

use Rector\CodingStyle\Rector\ClassMethod\NewlineBeforeNewAssignSetRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class NewlineBeforeNewAssignSetRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/skip_false.php.inc'];
        yield [__DIR__ . '/Fixture/skip_already_nop.php.inc'];
        yield [__DIR__ . '/Fixture/skip_just_one.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return NewlineBeforeNewAssignSetRector::class;
    }
}

<?php declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Include_\FollowRequireByDirRector;

use Iterator;
use Rector\CodingStyle\Rector\Include_\FollowRequireByDirRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class FollowRequireByDirRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/extra_dot.php.inc'];
        yield [__DIR__ . '/Fixture/phar.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return FollowRequireByDirRector::class;
    }
}

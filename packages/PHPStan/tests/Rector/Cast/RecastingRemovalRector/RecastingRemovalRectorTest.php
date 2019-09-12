<?php declare(strict_types=1);

namespace Rector\PHPStan\Tests\Rector\Cast\RecastingRemovalRector;

use Rector\PHPStan\Rector\Cast\RecastingRemovalRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RecastingRemovalRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function getRectorClass(): string
    {
        return RecastingRemovalRector::class;
    }

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/fixture2.php.inc'];
    }
}

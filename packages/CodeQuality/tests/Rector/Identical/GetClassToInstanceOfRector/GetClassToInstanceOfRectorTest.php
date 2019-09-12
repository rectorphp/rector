<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Identical\GetClassToInstanceOfRector;

use Rector\CodeQuality\Rector\Identical\GetClassToInstanceOfRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class GetClassToInstanceOfRectorTest extends AbstractRectorTestCase
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
        return GetClassToInstanceOfRector::class;
    }

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
    }
}

<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Concat\RemoveConcatAutocastRector;

use Rector\DeadCode\Rector\Concat\RemoveConcatAutocastRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveConcatAutocastRectorTest extends AbstractRectorTestCase
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
    }

    protected function getRectorClass(): string
    {
        return RemoveConcatAutocastRector::class;
    }
}

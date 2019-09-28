<?php declare(strict_types=1);

namespace Rector\NetteToSymfony\Tests\Rector\Class_\NetteFormToSymfonyFormRector;

use Iterator;
use Rector\NetteToSymfony\Rector\Class_\NetteFormToSymfonyFormRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class NetteFormToSymfonyFormRectorTest extends AbstractRectorTestCase
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
    }

    protected function getRectorClass(): string
    {
        return NetteFormToSymfonyFormRector::class;
    }
}

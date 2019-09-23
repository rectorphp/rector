<?php declare(strict_types=1);

namespace Rector\Php74\Tests\Rector\FuncCall\MbStrrposEncodingArgumentPositionRector;

use Rector\Php74\Rector\FuncCall\MbStrrposEncodingArgumentPositionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class MbStrrposEncodingArgumentPositionRectorTest extends AbstractRectorTestCase
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
        return MbStrrposEncodingArgumentPositionRector::class;
    }
}

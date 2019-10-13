<?php

declare(strict_types=1);

namespace Rector\Php74\Tests\Rector\FuncCall\MbStrrposEncodingArgumentPositionRector;

use Iterator;
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

    public function provideDataForTest(): Iterator
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return MbStrrposEncodingArgumentPositionRector::class;
    }
}

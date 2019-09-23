<?php declare(strict_types=1);

namespace Rector\Php74\Tests\Rector\FuncCall\ArrayKeyExistsOnPropertyRector;

use Rector\Php74\Rector\FuncCall\ArrayKeyExistsOnPropertyRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ArrayKeyExistsOnPropertyRectorTest extends AbstractRectorTestCase
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
        return ArrayKeyExistsOnPropertyRector::class;
    }
}

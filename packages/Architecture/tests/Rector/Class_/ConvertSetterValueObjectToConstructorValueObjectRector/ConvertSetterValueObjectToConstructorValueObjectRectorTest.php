<?php declare(strict_types=1);

namespace Rector\Architecture\Tests\Rector\Class_\ConvertSetterValueObjectToConstructorValueObjectRector;

use Rector\Architecture\Rector\Class_\ConvertSetterValueObjectToConstructorValueObjectRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ConvertSetterValueObjectToConstructorValueObjectRectorTest extends AbstractRectorTestCase
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
        return ConvertSetterValueObjectToConstructorValueObjectRector::class;
    }
}

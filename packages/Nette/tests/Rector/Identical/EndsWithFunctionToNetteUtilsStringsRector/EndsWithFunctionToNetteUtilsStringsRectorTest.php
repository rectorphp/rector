<?php declare(strict_types=1);

namespace Rector\Nette\Tests\Rector\Identical\EndsWithFunctionToNetteUtilsStringsRector;

use Rector\Nette\Rector\Identical\EndsWithFunctionToNetteUtilsStringsRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class EndsWithFunctionToNetteUtilsStringsRectorTest extends AbstractRectorTestCase
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
        return EndsWithFunctionToNetteUtilsStringsRector::class;
    }
}

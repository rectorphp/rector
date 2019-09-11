<?php declare(strict_types=1);

namespace Rector\Nette\Tests\Rector\FuncCall\SubstrStrlenFunctionToNetteUtilsStringsRector;

use Rector\Nette\Rector\FuncCall\SubstrStrlenFunctionToNetteUtilsStringsRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SubstrStrlenFunctionToNetteUtilsStringsRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/substr.php.inc'];
        yield [__DIR__ . '/Fixture/strlen.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return SubstrStrlenFunctionToNetteUtilsStringsRector::class;
    }
}

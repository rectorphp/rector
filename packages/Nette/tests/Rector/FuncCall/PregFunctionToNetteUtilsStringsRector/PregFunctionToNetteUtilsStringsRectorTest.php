<?php declare(strict_types=1);

namespace Rector\Nette\Tests\Rector\FuncCall\PregFunctionToNetteUtilsStringsRector;

use Iterator;
use Rector\Nette\Rector\FuncCall\PregFunctionToNetteUtilsStringsRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class PregFunctionToNetteUtilsStringsRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/preg_match.php.inc'];
        yield [__DIR__ . '/Fixture/preg_match_all.php.inc'];
        yield [__DIR__ . '/Fixture/preg_split.php.inc'];
        yield [__DIR__ . '/Fixture/preg_replace.php.inc'];
        yield [__DIR__ . '/Fixture/preg_replace_callback.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return PregFunctionToNetteUtilsStringsRector::class;
    }
}

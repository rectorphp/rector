<?php declare(strict_types=1);

namespace Rector\Nette\Tests\Rector\FuncCall\JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector;

use Rector\Nette\Rector\FuncCall\JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/json_decode.php.inc'];
        yield [__DIR__ . '/Fixture/json_encode.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector::class;
    }
}

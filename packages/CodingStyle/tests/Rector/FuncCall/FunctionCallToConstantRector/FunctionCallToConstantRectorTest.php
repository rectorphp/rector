<?php declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\FuncCall\FunctionCallToConstantRector;

use Iterator;
use Rector\CodingStyle\Rector\FuncCall\FunctionCallToConstantRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class FunctionCallToConstantRectorTest extends AbstractRectorTestCase
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

    protected function getRectorsWithConfiguration(): array
    {
        return [
            FunctionCallToConstantRector::class => [
                '$functionsToConstants' => [
                    'php_sapi_name' => 'PHP_SAPI',
                    'pi' => 'M_PI',
                ],
            ],
        ];
    }
}

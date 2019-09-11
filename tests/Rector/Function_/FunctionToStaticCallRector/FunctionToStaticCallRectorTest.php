<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Function_\FunctionToStaticCallRector;

use Rector\Rector\Function_\FunctionToStaticCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class FunctionToStaticCallRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/fixture2.php.inc'];
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            FunctionToStaticCallRector::class => [
                '$functionToStaticCall' => [
                    'view' => ['SomeStaticClass', 'render'],
                    'SomeNamespaced\view' => ['AnotherStaticClass', 'render'],
                ],
            ],
        ];
    }
}

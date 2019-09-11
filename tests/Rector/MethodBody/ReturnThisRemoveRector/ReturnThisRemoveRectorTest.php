<?php declare(strict_types=1);

namespace Rector\Tests\Rector\MethodBody\ReturnThisRemoveRector;

use Rector\Rector\MethodBody\ReturnThisRemoveRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ReturnThisRemoveRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/fixture3.php.inc'];
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ReturnThisRemoveRector::class => [
                '$classesToDefluent' => [
                    'Rector\Tests\Rector\MethodBody\ReturnThisRemoveRector\SomeClass',
                    'Rector\Tests\Rector\MethodBody\ReturnThisRemoveRector\SomeClassWithReturnAnnotations',
                ],
            ],
        ];
    }
}

<?php declare(strict_types=1);

namespace Rector\Tests\Rector\New_\NewToStaticCallRector;

use Iterator;
use Rector\Rector\New_\NewToStaticCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\New_\NewToStaticCallRector\Source\FromNewClass;
use Rector\Tests\Rector\New_\NewToStaticCallRector\Source\IntoStaticClass;

final class NewToStaticCallRectorTest extends AbstractRectorTestCase
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

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            NewToStaticCallRector::class => [
                '$typeToStaticCalls' => [
                    FromNewClass::class => [IntoStaticClass::class, 'run'],
                ],
            ],
        ];
    }
}

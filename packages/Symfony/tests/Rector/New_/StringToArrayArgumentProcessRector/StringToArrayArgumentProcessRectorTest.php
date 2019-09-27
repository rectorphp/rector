<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\New_\StringToArrayArgumentProcessRector;

use Iterator;
use Rector\Symfony\Rector\New_\StringToArrayArgumentProcessRector;
use Rector\Symfony\Tests\Rector\New_\StringToArrayArgumentProcessRector\Source\Process;
use Rector\Symfony\Tests\Rector\New_\StringToArrayArgumentProcessRector\Source\ProcessHelper;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class StringToArrayArgumentProcessRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/fixture2.php.inc'];
        yield [__DIR__ . '/Fixture/fixture3.php.inc'];
        yield [__DIR__ . '/Fixture/fixture4.php.inc'];
        yield [__DIR__ . '/Fixture/with_sprintf.php.inc'];
        yield [__DIR__ . '/Fixture/skip_anonymous_class.php.inc'];
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            StringToArrayArgumentProcessRector::class => [
                '$processClass' => Process::class,
                '$processHelperClass' => ProcessHelper::class,
            ],
        ];
    }
}

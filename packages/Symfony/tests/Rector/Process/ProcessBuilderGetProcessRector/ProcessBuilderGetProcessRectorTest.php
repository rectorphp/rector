<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Process\ProcessBuilderGetProcessRector;

use Rector\Symfony\Rector\Process\ProcessBuilderGetProcessRector;
use Rector\Symfony\Tests\Rector\Process\ProcessBuilderGetProcessRector\Source\ProcessBuilder;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ProcessBuilderGetProcessRectorTest extends AbstractRectorTestCase
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

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ProcessBuilderGetProcessRector::class => [
                '$processBuilderClass' => ProcessBuilder::class,
            ],
        ];
    }
}

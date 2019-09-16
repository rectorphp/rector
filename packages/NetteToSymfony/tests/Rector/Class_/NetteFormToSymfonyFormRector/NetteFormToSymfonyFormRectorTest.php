<?php declare(strict_types=1);

namespace Rector\NetteToSymfony\Tests\Rector\Class_\NetteFormToSymfonyFormRector;

use Rector\NetteToSymfony\Rector\Class_\NetteFormToSymfonyFormRector;
use Rector\NetteToSymfony\Tests\Rector\Class_\NetteFormToSymfonyFormRector\Source\NettePresenter;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class NetteFormToSymfonyFormRectorTest extends AbstractRectorTestCase
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
            NetteFormToSymfonyFormRector::class => [
                '$presenterClass' => NettePresenter::class,
            ],
        ];
    }
}

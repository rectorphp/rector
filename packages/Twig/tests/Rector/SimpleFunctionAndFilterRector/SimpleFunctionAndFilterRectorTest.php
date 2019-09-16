<?php declare(strict_types=1);

namespace Rector\Twig\Tests\Rector\SimpleFunctionAndFilterRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Twig\Rector\SimpleFunctionAndFilterRector;
use Rector\Twig\Tests\Rector\SimpleFunctionAndFilterRector\Source\TwigExtension;
use Rector\Twig\Tests\Rector\SimpleFunctionAndFilterRector\Source\TwigFilterMethod;
use Rector\Twig\Tests\Rector\SimpleFunctionAndFilterRector\Source\TwigFunctionMethod;
use Rector\Twig\Tests\Rector\SimpleFunctionAndFilterRector\Source\TwigSimpleFilter;
use Rector\Twig\Tests\Rector\SimpleFunctionAndFilterRector\Source\TwigSimpleFunction;

final class SimpleFunctionAndFilterRectorTest extends AbstractRectorTestCase
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
            SimpleFunctionAndFilterRector::class => [
                '$twigExtensionClass' => TwigExtension::class,
                '$oldToNewClasses' => [
                    TwigFunctionMethod::class => TwigSimpleFunction::class,
                    TwigFilterMethod::class => TwigSimpleFilter::class,
                ],
            ],
        ];
    }
}

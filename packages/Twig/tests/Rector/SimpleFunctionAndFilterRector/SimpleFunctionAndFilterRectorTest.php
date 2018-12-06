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
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/fixture2.php.inc',
            __DIR__ . '/Fixture/fixture3.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return SimpleFunctionAndFilterRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return ['arguments' => [
            '$twigExtensionClass' => TwigExtension::class,
            '$oldToNewClasses' => [
                TwigFunctionMethod::class => TwigSimpleFunction::class,
                TwigFilterMethod::class => TwigSimpleFilter::class,
            ],
        ]];
    }
}

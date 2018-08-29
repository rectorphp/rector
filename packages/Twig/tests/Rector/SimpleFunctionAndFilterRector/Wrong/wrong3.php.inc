<?php declare(strict_types=1);

namespace  Rector\Twig\Tests\Rector\SimpleFunctionAndFilterRector\Wrong;

use Rector\Twig\Tests\Rector\SimpleFunctionAndFilterRector\Source\TwigExtension;
use Rector\Twig\Tests\Rector\SimpleFunctionAndFilterRector\Source\TwigFilterMethod;

final class SomeExtension extends TwigExtension
{
    public function getFilters()
    {
        return [
            'sphinx_highlight' => new TwigFilterMethod([$this, 'sphinx_highlight'], ['is_safe' => ['html']]),
        ];
    }
}

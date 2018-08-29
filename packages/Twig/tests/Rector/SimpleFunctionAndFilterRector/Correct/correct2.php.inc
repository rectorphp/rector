<?php declare(strict_types=1);

namespace  Rector\Twig\Tests\Rector\SimpleFunctionAndFilterRector\Wrong;

use Rector\Twig\Tests\Rector\SimpleFunctionAndFilterRector\Source\TwigExtension;
use Rector\Twig\Tests\Rector\SimpleFunctionAndFilterRector\Source\TwigFilterMethod;

final class SomeExtension extends TwigExtension
{
    public function getFilters()
    {
        return [
            new \Rector\Twig\Tests\Rector\SimpleFunctionAndFilterRector\Source\TwigSimpleFilter('is_mobile', [$this, 'isMobile']),
        ];
    }
}

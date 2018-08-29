<?php declare(strict_types=1);

namespace  Rector\Twig\Tests\Rector\SimpleFunctionAndFilterRector\Wrong;

use Rector\Twig\Tests\Rector\SimpleFunctionAndFilterRector\Source\TwigExtension;
use Rector\Twig\Tests\Rector\SimpleFunctionAndFilterRector\Source\TwigFunctionMethod;

final class SomeExtension extends TwigExtension
{
    public function getFunctions()
    {
        return [
            'is_mobile' => new TwigFunctionMethod($this, 'isMobile'),
        ];
    }
}

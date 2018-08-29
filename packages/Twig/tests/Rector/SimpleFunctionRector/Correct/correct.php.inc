<?php declare(strict_types=1);

namespace  Rector\Twig\Tests\Rector\SimpleFunctionRector\Wrong;

use Rector\Twig\Tests\Rector\SimpleFunctionRector\Source\TwigExtension;
use Rector\Twig\Tests\Rector\SimpleFunctionRector\Source\TwigFunctionMethod;

final class SomeExtension extends TwigExtension
{
    public function getFunctions()
    {
        return [
            new \Rector\Twig\Tests\Rector\SimpleFunctionRector\Source\TwigSimpleFunction('is_mobile', [$this, 'isMobile']),
        ];
    }
}

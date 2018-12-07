<?php

namespace  Rector\Twig\Tests\Rector\SimpleFunctionAndFilterRector\Fixture;

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

?>
-----
<?php

namespace  Rector\Twig\Tests\Rector\SimpleFunctionAndFilterRector\Fixture;

use Rector\Twig\Tests\Rector\SimpleFunctionAndFilterRector\Source\TwigExtension;
use Rector\Twig\Tests\Rector\SimpleFunctionAndFilterRector\Source\TwigFunctionMethod;

final class SomeExtension extends TwigExtension
{
    public function getFunctions()
    {
        return [
            new \Rector\Twig\Tests\Rector\SimpleFunctionAndFilterRector\Source\TwigSimpleFunction('is_mobile', [$this, 'isMobile']),
        ];
    }
}

?>

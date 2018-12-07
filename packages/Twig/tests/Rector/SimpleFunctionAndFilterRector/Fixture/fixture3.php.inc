<?php

namespace  Rector\Twig\Tests\Rector\SimpleFunctionAndFilterRector\Fixture;

use Rector\Twig\Tests\Rector\SimpleFunctionAndFilterRector\Source\TwigExtension;
use Rector\Twig\Tests\Rector\SimpleFunctionAndFilterRector\Source\TwigFilterMethod;

final class SomeExtension3 extends TwigExtension
{
    public function getFilters()
    {
        return [
            'sphinx_highlight' => new TwigFilterMethod([$this, 'sphinx_highlight'], ['is_safe' => ['html']]),
        ];
    }
}

?>
-----
<?php

namespace  Rector\Twig\Tests\Rector\SimpleFunctionAndFilterRector\Fixture;

use Rector\Twig\Tests\Rector\SimpleFunctionAndFilterRector\Source\TwigExtension;
use Rector\Twig\Tests\Rector\SimpleFunctionAndFilterRector\Source\TwigFilterMethod;

final class SomeExtension3 extends TwigExtension
{
    public function getFilters()
    {
        return [
            new \Rector\Twig\Tests\Rector\SimpleFunctionAndFilterRector\Source\TwigSimpleFilter('sphinx_highlight', [$this, 'sphinx_highlight'], ['is_safe' => ['html']]),
        ];
    }
}

?>

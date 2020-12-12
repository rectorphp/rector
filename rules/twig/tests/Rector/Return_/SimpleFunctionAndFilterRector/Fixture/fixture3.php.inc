<?php

namespace Rector\Twig\Tests\Rector\Return_\SimpleFunctionAndFilterRector\Fixture;

use Twig_Extension;
use Twig_Filter_Method;

final class SomeExtension3 extends Twig_Extension
{
    public function getFilters()
    {
        return [
            'sphinx_highlight' => new Twig_Filter_Method([$this, 'sphinx_highlight'], ['is_safe' => ['html']]),
        ];
    }
}

?>
-----
<?php

namespace Rector\Twig\Tests\Rector\Return_\SimpleFunctionAndFilterRector\Fixture;

use Twig_Extension;
use Twig_Filter_Method;

final class SomeExtension3 extends Twig_Extension
{
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('sphinx_highlight', [$this, 'sphinx_highlight'], ['is_safe' => ['html']]),
        ];
    }
}

?>

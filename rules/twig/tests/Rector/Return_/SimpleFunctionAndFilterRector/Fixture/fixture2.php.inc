<?php

namespace Rector\Twig\Tests\Rector\Return_\SimpleFunctionAndFilterRector\Fixture;

use Twig_Extension;
use Twig_Filter_Method;

final class SomeExtension2 extends Twig_Extension
{
    public function getFilters()
    {
        return [
            'is_mobile' => new Twig_Filter_Method($this, 'isMobile'),
        ];
    }
}

?>
-----
<?php

namespace Rector\Twig\Tests\Rector\Return_\SimpleFunctionAndFilterRector\Fixture;

use Twig_Extension;
use Twig_Filter_Method;

final class SomeExtension2 extends Twig_Extension
{
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('is_mobile', [$this, 'isMobile']),
        ];
    }
}

?>

<?php

namespace Rector\Tests\Rector\Class_\ClassReplacerRector\Fixture;

use Manual_Twig_Filter;
use Manual\Twig\TwigFilter;

final class ShortUrlExtension
{
    /**
     * @return Manual_Twig_Filter[]
     */
    public function getFilters(): array
    {
        return [
            new Manual_Twig_Filter('shortUrl', [$this, 'shortUrl']),
        ];
    }
}

?>
-----
<?php

namespace Rector\Tests\Rector\Class_\ClassReplacerRector\Fixture;

use Manual_Twig_Filter;
use Manual\Twig\TwigFilter;

final class ShortUrlExtension
{
    /**
     * @return Manual_Twig_Filter[]
     */
    public function getFilters(): array
    {
        return [
            new \Manual\Twig\TwigFilter('shortUrl', [$this, 'shortUrl']),
        ];
    }
}

?>

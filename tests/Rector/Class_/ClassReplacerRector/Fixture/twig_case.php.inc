<?php

namespace Rector\Tests\Rector\Class_\ClassReplacerRector\Fixture;

use Manual_Twig_Filter;
use Manual\Twig\TwigFilter;
use Rector\Tests\Rector\Class_\ClassReplacerRector\Source\AbstractManualExtension;

final class ShortUrlExtension extends AbstractManualExtension
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
use Rector\Tests\Rector\Class_\ClassReplacerRector\Source\AbstractManualExtension;

final class ShortUrlExtension extends AbstractManualExtension
{
    /**
     * @return \Manual\Twig\TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new \Manual\Twig\TwigFilter('shortUrl', [$this, 'shortUrl']),
        ];
    }
}

?>

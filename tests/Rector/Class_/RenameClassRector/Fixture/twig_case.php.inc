<?php

namespace Rector\Tests\Rector\Class_\RenameClassRector\Fixture;

use Manual_Twig_Filter;
use Manual\Twig\TwigFilter;
use Rector\Tests\Rector\Class_\RenameClassRector\Source\AbstractManualExtension;

final class ShortUrlExtension extends AbstractManualExtension
{
    /**
     * @var \Twig_Extension_Sandbox
     */
    public $sandboxExtension;

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

/**
 * @var \Twig_Extension_Sandbox
 */
function someFunctionWithParamTwig(\Twig_Extension_Sandbox $argument)
{
}

?>
-----
<?php

namespace Rector\Tests\Rector\Class_\RenameClassRector\Fixture;

use Manual_Twig_Filter;
use Manual\Twig\TwigFilter;
use Rector\Tests\Rector\Class_\RenameClassRector\Source\AbstractManualExtension;

final class ShortUrlExtension extends AbstractManualExtension
{
    /**
     * @var \Twig\Extension\SandboxExtension
     */
    public $sandboxExtension;

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

/**
 * @var \Twig\Extension\SandboxExtension
 */
function someFunctionWithParamTwig(\Twig\Extension\SandboxExtension $argument)
{
}

?>

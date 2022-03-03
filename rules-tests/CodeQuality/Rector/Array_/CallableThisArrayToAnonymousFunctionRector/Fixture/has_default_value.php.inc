<?php

namespace Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

use Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Source\SomeClassWithDefaultParamValue;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class HasDefaultValue extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('test', [SomeClassWithDefaultParamValue::class, "run"]),
        ];
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

use Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Source\SomeClassWithDefaultParamValue;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class HasDefaultValue extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('test', function ($a, $b = ['test']) {
                return (new \Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Source\SomeClassWithDefaultParamValue())->run($a, $b);
            }),
        ];
    }
}

?>

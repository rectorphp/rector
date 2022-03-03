<?php

namespace Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

use Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Source\SomeClassWithDefaultParamValue;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class HasDefaultValue2 extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('test', [$this, "run"]),
        ];
    }

    public function run($a, $b = "test")
    {
        return $a . $b;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

use Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Source\SomeClassWithDefaultParamValue;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class HasDefaultValue2 extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('test', function ($a, $b = "test") {
                return $this->run($a, $b);
            }),
        ];
    }

    public function run($a, $b = "test")
    {
        return $a . $b;
    }
}

?>

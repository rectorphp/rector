<?php

namespace Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

use Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Source\SortingClass;

final class ExternalClass
{
    /**
     * @var SortingClass
     */
    private $sortingClass;

    public function __construct(SortingClass $sortingClass)
    {
        $this->sortingClass = $sortingClass;
    }

    public function noGo($values)
    {
        $sortingClass = new SortingClass();

        usort($values, [$this->sortingClass, 'protectedSort']);
        usort($values, [$sortingClass, 'privateSort']);
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

use Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Source\SortingClass;

final class ExternalClass
{
    /**
     * @var SortingClass
     */
    private $sortingClass;

    public function __construct(SortingClass $sortingClass)
    {
        $this->sortingClass = $sortingClass;
    }

    public function noGo($values)
    {
        $sortingClass = new SortingClass();

        usort($values, function ($a, $b) {
            return $this->sortingClass->protectedSort($a, $b);
        });
        usort($values, function ($a, $b) use ($sortingClass) {
            return $sortingClass->privateSort($a, $b);
        });
    }
}

?>

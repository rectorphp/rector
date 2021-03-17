<?php

namespace Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

use Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Source\SortingClass;

class AnotherClass
{
    /**
     * @var SortingClass
     */
    private $sortingClass;

    public function __construct(SortingClass $sortingClass)
    {
        $this->sortingClass = $sortingClass;
    }

    public function go($values)
    {
        $sortingClass = new SortingClass();

        usort($values, [$this->sortingClass, 'publicSort']);

        usort($values, [$sortingClass, 'publicSort']);
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

use Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Source\SortingClass;

class AnotherClass
{
    /**
     * @var SortingClass
     */
    private $sortingClass;

    public function __construct(SortingClass $sortingClass)
    {
        $this->sortingClass = $sortingClass;
    }

    public function go($values)
    {
        $sortingClass = new SortingClass();

        usort($values, function ($a, $b) {
            return $this->sortingClass->publicSort($a, $b);
        });

        usort($values, function ($a, $b) use ($sortingClass) {
            return $sortingClass->publicSort($a, $b);
        });
    }
}

?>

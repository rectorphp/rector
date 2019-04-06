<?php

namespace Rector\CodeQuality\Tests\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

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

final class SortingClass
{
    public function publicSort($a, $b)
    {
        return $a <=> $b;
    }

    protected function protectedSort($a, $b)
    {
        return $a <=> $b;
    }

    private function privateSort($a, $b)
    {
        return $a <=> $b;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

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

final class SortingClass
{
    public function publicSort($a, $b)
    {
        return $a <=> $b;
    }

    protected function protectedSort($a, $b)
    {
        return $a <=> $b;
    }

    private function privateSort($a, $b)
    {
        return $a <=> $b;
    }
}

?>

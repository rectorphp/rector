<?php

namespace Rector\CodeQuality\Tests\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

use Rector\CodeQuality\Tests\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Source\SortingClass;

class SkipAnotherClass
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

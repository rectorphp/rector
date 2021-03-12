<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Source;

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

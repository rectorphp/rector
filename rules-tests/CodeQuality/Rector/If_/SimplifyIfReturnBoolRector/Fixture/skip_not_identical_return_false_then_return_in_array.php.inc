<?php

namespace Rector\Tests\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector\Fixture;

class SkipNotIdenticalReturnFalseThenReturnInArray
{
    private const POSSIBLE_DELIMITERS = ['#', '~', '/'];

    public function run($firstChar, $lastChar)
    {
        if ($firstChar !== $lastChar) {
            return false;
        }

        return in_array($firstChar, self::POSSIBLE_DELIMITERS, true);
    }
}

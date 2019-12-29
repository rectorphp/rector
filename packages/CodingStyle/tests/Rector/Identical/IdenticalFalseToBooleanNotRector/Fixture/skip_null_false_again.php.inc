<?php

namespace Rector\CodingStyle\Tests\Rector\Identical\IdenticalFalseToBooleanNotRector\Fixture;

use DateTime;

class SkipNullFalseAgain
{
    public function someFunction(string $startStr, string $endStr)
    {
        $start = !empty($startStr) ? 1 : null;
        $end = !empty($endStr) ? 2 : null;

        if (false === $end) {
        } elseif (false === $start) {
        }
    }
}

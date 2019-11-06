<?php

namespace Rector\CodingStyle\Tests\Rector\Identical\IdenticalFalseToBooleanNotRector\Fixture;

final class SkipMixed
{
    /**
     * @param mixed $attribute
     */
    public function run($attribute)
    {
        if ($attribute === null) {
            return 'null';
        } elseif ($attribute === false) {
            return 'false';
        }
    }
}

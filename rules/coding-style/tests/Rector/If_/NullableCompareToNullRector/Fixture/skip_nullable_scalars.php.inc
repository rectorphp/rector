<?php

namespace Rector\CodingStyle\Tests\Rector\If_\NullableCompareToNullRector\Fixture;

class SkipNullableScalars
{
    public function run(?array $arrayOrNull)
    {
        if (! $arrayOrNull) {
            return 'no item or null';
        }

        if ($arrayOrNull) {
            return 'non-empty array';
        }
    }

    public function runString(?string $stringOrNull)
    {
        if (! $stringOrNull) {
            return 'empty string or null';
        }

        if ($stringOrNull) {
            return 'non-empty string';
        }
    }

    /**
     * @param null|object[] $arrayOrNull
     */
    public function runArrayWithObjects(?array $arrayOrNull)
    {
        if (! $arrayOrNull) {
            return 'no item or null';
        }

        if ($arrayOrNull) {
            return 'non-empty array';
        }
    }
}

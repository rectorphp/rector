<?php

namespace Rector\Php71\Tests\Rector\FuncCall\CountOnNullRector\Fixture;

class SkipArrayMerge
{
    public function run(string $string, array $regexes): ?array
    {
        $results = [];

        foreach ($regexes as $toFind) {
            if (false !== preg_match_all($toFind, $string, $result)) {
                $results[] = $result[0];
            }
        }

        $results = array_merge(...$results);

        return count($results) > 0 ? $results : null;
    }
}

<?php

namespace Rector\Tests\CodeQuality\Rector\Foreach_\SimplifyForeachToArrayFilterRector\Fixture;

final class SkipSameVar
{
    public function run()
    {
        $responseData = [];
        foreach ($responseData as $key => $value) {
            if (is_string($value)) {
                $responseData[$key] = $value;
            }
        }
    }
}

?>

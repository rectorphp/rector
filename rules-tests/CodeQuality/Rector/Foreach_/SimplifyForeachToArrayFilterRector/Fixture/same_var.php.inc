<?php

namespace Rector\Tests\CodeQuality\Rector\Foreach_\SimplifyForeachToArrayFilterRector\Fixture;

$responseData = [];
foreach ($responseData as $key => $value) {
    if (is_string($value)) {
        $responseData[$key] = $value;
    }
}

?>

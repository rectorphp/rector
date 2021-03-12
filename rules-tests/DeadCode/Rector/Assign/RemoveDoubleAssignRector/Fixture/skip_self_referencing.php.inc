<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveDoubleAssignRector\Fixture;

$value = 1;
$value = $value + 1;
$value = $value + 1;

?>

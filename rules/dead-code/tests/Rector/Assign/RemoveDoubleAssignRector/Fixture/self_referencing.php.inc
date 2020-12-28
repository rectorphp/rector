<?php

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveDoubleAssignRector\Fixture;

$value = 1;
$value = $value + 1;
$value = $value + 1;

?>

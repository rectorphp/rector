<?php

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveDoubleAssignRector\DifferentValue;

$value = 1;
$value = $value + 1;
$value = $value + 1;

?>

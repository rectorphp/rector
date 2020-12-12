<?php

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveDoubleAssignRector\DifferentValue;

$value = 1;
$value = 2;

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveDoubleAssignRector\DifferentValue;

$value = 2;

?>

<?php

namespace Rector\DeadCode\Tests\Rector\MethodCall\RemoveDefaultArgumentValueRector\Fixture;

function someLocalFunction($items = [])
{
}

someLocalFunction([]);

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\MethodCall\RemoveDefaultArgumentValueRector\Fixture;

function someLocalFunction($items = [])
{
}

someLocalFunction();

?>

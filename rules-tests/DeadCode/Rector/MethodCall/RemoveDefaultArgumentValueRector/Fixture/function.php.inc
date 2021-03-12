<?php

namespace Rector\Tests\DeadCode\Rector\MethodCall\RemoveDefaultArgumentValueRector\Fixture;

function someLocalFunction($items = [])
{
}

someLocalFunction([]);

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\MethodCall\RemoveDefaultArgumentValueRector\Fixture;

function someLocalFunction($items = [])
{
}

someLocalFunction();

?>

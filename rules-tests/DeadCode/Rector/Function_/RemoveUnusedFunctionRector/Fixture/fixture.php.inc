<?php

namespace Rector\Tests\DeadCode\Rector\Function_\RemoveUnusedFunctionRector\Fixture;

function removeMe()
{
}

function useMe()
{
}

useMe();

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Function_\RemoveUnusedFunctionRector\Fixture;

function useMe()
{
}

useMe();

?>

<?php

namespace Rector\DeadCode\Tests\Rector\Function_\RemoveUnusedFunctionRector\Fixture;

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

namespace Rector\DeadCode\Tests\Rector\Function_\RemoveUnusedFunctionRector\Fixture;

function useMe()
{
}

useMe();

?>

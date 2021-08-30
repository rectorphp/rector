<?php

namespace Rector\Tests\CodingStyle\Rector\Stmt\NewlineAfterStatementRector\Fixture;

function run()
{
}
function run2()
{
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Stmt\NewlineAfterStatementRector\Fixture;

function run()
{
}

function run2()
{
}

?>

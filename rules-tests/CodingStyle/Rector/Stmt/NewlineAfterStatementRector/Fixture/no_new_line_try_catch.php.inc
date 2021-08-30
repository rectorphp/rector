<?php

namespace Rector\Tests\CodingStyle\Rector\Stmt\NewlineAfterStatementRector\Fixture;

function run()
{
    try {

    } catch (Exception $e) {

    }
    return true;
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Stmt\NewlineAfterStatementRector\Fixture;

function run()
{
    try {

    } catch (Exception $e) {

    }

    return true;
}

?>

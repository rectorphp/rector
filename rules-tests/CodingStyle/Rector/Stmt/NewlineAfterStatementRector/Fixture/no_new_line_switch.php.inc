<?php

namespace Rector\Tests\CodingStyle\Rector\Stmt\NewlineAfterStatementRector\Fixture;

function run($data)
{
    switch ($data) {
        case 'a':
            echo 'test';
            break;
        default:
            echo 'test 1';
    }
    return true;
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Stmt\NewlineAfterStatementRector\Fixture;

function run($data)
{
    switch ($data) {
        case 'a':
            echo 'test';
            break;
        default:
            echo 'test 1';
    }

    return true;
}

?>

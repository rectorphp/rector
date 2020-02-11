<?php

namespace Rector\Php70\Tests\Rector\Switch_\ReduceMultipleDefaultSwitchRector\Fixture;

function multipleDefaults()
{
    switch ($expr) {
        case 5:
            echo 5;
            break;
        default:
            echo "Hello World";

        default:
            echo "Goodbye Moon!";
            break;
    }
}

function multipleDefaultsSoMany()
{
    switch ($expr) {
        case 5:
            echo 5;
            break;
        default:
            echo "Hello World";
        default:
            echo "Hi";
            break;
        default:
            echo "Goodbye Moon!";
            break;
    }
}

?>
-----
<?php

namespace Rector\Php70\Tests\Rector\Switch_\ReduceMultipleDefaultSwitchRector\Fixture;

function multipleDefaults()
{
    switch ($expr) {
        case 5:
            echo 5;
            break;

        default:
            echo "Goodbye Moon!";
            break;
    }
}

function multipleDefaultsSoMany()
{
    switch ($expr) {
        case 5:
            echo 5;
            break;
        default:
            echo "Goodbye Moon!";
            break;
    }
}

?>

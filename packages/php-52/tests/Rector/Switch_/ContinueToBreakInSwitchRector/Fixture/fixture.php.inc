<?php

namespace Rector\Php52\Tests\Rector\Switch_\ContinueToBreakInSwitchRector\Fixture;

function some_run($value)
{
    switch ($value) {
        case 1:
            echo 'Hi';
            continue;
        case 2:
            echo 'Hello';
            break;
    }
}

?>
-----
<?php

namespace Rector\Php52\Tests\Rector\Switch_\ContinueToBreakInSwitchRector\Fixture;

function some_run($value)
{
    switch ($value) {
        case 1:
            echo 'Hi';
            break;
        case 2:
            echo 'Hello';
            break;
    }
}

?>

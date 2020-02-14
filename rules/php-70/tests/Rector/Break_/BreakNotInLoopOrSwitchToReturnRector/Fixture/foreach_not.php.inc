<?php

namespace Rector\Php70\Tests\Rector\Break_\BreakNotInLoopOrSwitchToReturnRector\Fixture;

function break_missplaced_function_foreach()
{
    $ip_block = [];

    foreach($ip_block as $ip)
        echo $ip . "<br/>\n"; //just to verify file has been read

    {
        if($client_ip == $ip)
        {
            $blocked = true;
            break;
        }
        // [...]
    }
}

?>
-----
<?php

namespace Rector\Php70\Tests\Rector\Break_\BreakNotInLoopOrSwitchToReturnRector\Fixture;

function break_missplaced_function_foreach()
{
    $ip_block = [];

    foreach($ip_block as $ip)
        echo $ip . "<br/>\n"; //just to verify file has been read

    {
        if($client_ip == $ip)
        {
            $blocked = true;
            return;
        }
        // [...]
    }
}

?>

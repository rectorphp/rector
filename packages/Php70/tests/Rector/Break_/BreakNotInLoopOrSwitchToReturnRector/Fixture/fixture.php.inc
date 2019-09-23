<?php

namespace Rector\Php70\Tests\Rector\Break_\BreakNotInLoopOrSwitchToReturnRector\Fixture;

function break_missplaced_function()
{
    $zhrs = abs($gmt)/3600;
    $hrs = floor($zhrs);
    if ($isphp5)
        return sprintf('%s%02d%02d',($gmt<=0)?'+':'-',floor($zhrs),($zhrs-$hrs)*60);
    else
        return sprintf('%s%02d%02d',($gmt<0)?'+':'-',floor($zhrs),($zhrs-$hrs)*60);
    break;
}

function break_missplace_again()
{
    $errors = [];
    if (isset($errors)) {
        foreach ($errors as $error)
        {
            echo $error;
        }
    } else {
        break 1;
    }
}

?>
-----
<?php

namespace Rector\Php70\Tests\Rector\Break_\BreakNotInLoopOrSwitchToReturnRector\Fixture;

function break_missplaced_function()
{
    $zhrs = abs($gmt)/3600;
    $hrs = floor($zhrs);
    if ($isphp5)
        return sprintf('%s%02d%02d',($gmt<=0)?'+':'-',floor($zhrs),($zhrs-$hrs)*60);
    else
        return sprintf('%s%02d%02d',($gmt<0)?'+':'-',floor($zhrs),($zhrs-$hrs)*60);
}

function break_missplace_again()
{
    $errors = [];
    if (isset($errors)) {
        foreach ($errors as $error)
        {
            echo $error;
        }
    } else {
        return;
    }
}

?>

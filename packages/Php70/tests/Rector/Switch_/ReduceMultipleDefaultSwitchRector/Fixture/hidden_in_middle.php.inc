<?php

namespace Rector\Php70\Tests\Rector\Switch_\ReduceMultipleDefaultSwitchRector\Fixture;

function multipleDefaults2()
{
    switch (strtoupper($format)) {
        case 'A3':
            $format = array(841.89,1190.55);
            break;
        case 'A4': default: $format = array(595.28,841.89);
            break;
        case 'A5':
            $format = array(419.53,595.28);
            break;
        case 'ROYAL': // 'Royal' format paperback size 153x234mm
            $format=array(433.70,663.30 );
            break;

        default: $format = false;
    }
}

?>
-----
<?php

namespace Rector\Php70\Tests\Rector\Switch_\ReduceMultipleDefaultSwitchRector\Fixture;

function multipleDefaults2()
{
    switch (strtoupper($format)) {
        case 'A3':
            $format = array(841.89,1190.55);
            break;
        case 'A4':
            $format = array(595.28,841.89);
            break;
        case 'A5':
            $format = array(419.53,595.28);
            break;
        case 'ROYAL': // 'Royal' format paperback size 153x234mm
            $format=array(433.70,663.30 );
            break;

        default: $format = false;
    }
}

?>

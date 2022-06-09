<?php

namespace Rector\Tests\Php70\Rector\FuncCall\EregToPregMatchRector\Fixture;

function eregToPregMatch5()
{
    ereg_replace('[^0-9.-]', ' ', 'le 09.78-95 65/02');

}

?>
-----
<?php

namespace Rector\Tests\Php70\Rector\FuncCall\EregToPregMatchRector\Fixture;

function eregToPregMatch5()
{
    preg_replace('#[^0-9\.\-]#m', ' ', 'le 09.78-95 65/02');

}

?>

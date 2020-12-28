<?php

namespace Rector\DowngradePhp73\Tests\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class SkippedValues
{
    public function run($string)
    {
        $array = [1, 2, 3, [3, 4]];
        list(&$a, $b,, $d, &$e) = $array;
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp73\Tests\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class SkippedValues
{
    public function run($string)
    {
        $array = [1, 2, 3, [3, 4]];
        list($a, $b,, $d) = $array;
        $a =& $array[0];
        $e =& $array[4];
    }
}

?>

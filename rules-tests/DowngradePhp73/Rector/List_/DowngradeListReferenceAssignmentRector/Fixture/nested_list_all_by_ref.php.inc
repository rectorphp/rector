<?php

namespace Rector\Tests\DowngradePhp73\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class NestedListAllByRef
{
    public function run($string)
    {
        $array = [1, 2, [3, 4]];
        list(&$a, &$b, list(&$c, &$d)) = $array;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp73\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class NestedListAllByRef
{
    public function run($string)
    {
        $array = [1, 2, [3, 4]];
        $a =& $array[0];
        $b =& $array[1];
        $c =& $array[2][0];
        $d =& $array[2][1];
    }
}

?>

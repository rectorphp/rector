<?php

namespace Rector\DowngradePhp73\Tests\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class MultilevelNestedList
{
    public function run($string)
    {
        $array = [1, 2, [3, 4, [5, 6, [7, 8, 9]]]];
        list($a, $b, list(&$c, $d, list($e, $f, list($g, &$h, $i)))) = $array;
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp73\Tests\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class MultilevelNestedList
{
    public function run($string)
    {
        $array = [1, 2, [3, 4, [5, 6, [7, 8, 9]]]];
        list($a, $b, list($c, $d, list($e, $f, list($g, $h, $i)))) = $array;
        $c =& $array[2][0];
        $h =& $array[2][2][2][1];
    }
}

?>

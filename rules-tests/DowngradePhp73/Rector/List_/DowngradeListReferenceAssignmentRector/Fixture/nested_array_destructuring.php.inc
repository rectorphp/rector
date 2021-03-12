<?php

namespace Rector\Tests\DowngradePhp73\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class NestedArrayDestructuring
{
    public function run($string)
    {
        $array = [1, 2, [3, 4]];
        [&$a, $b, [&$c, $d]] = $array;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp73\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class NestedArrayDestructuring
{
    public function run($string)
    {
        $array = [1, 2, [3, 4]];
        [$a, $b, [$c, $d]] = $array;
        $a =& $array[0];
        $c =& $array[2][0];
    }
}

?>

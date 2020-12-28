<?php

namespace Rector\DowngradePhp73\Tests\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class ArrayDestructuringAllByRef
{
    public function run($string)
    {
        $array = [1, 2, 3];
        [&$c, &$d, &$e] = $array;
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp73\Tests\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class ArrayDestructuringAllByRef
{
    public function run($string)
    {
        $array = [1, 2, 3];
        $c =& $array[0];
        $d =& $array[1];
        $e =& $array[2];
    }
}

?>

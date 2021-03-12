<?php

namespace Rector\Tests\DowngradePhp73\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class ArrayDestructuringEndByRef
{
    public function run($string)
    {
        $array = [1, 2, 3];
        [&$c, $d, &$e] = $array;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp73\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class ArrayDestructuringEndByRef
{
    public function run($string)
    {
        $array = [1, 2, 3];
        [$c, $d] = $array;
        $c =& $array[0];
        $e =& $array[2];
    }
}

?>

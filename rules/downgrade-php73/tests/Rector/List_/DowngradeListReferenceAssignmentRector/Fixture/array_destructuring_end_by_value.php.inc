<?php

namespace Rector\DowngradePhp73\Tests\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class ArrayDestructuringEndByValue
{
    public function run($string)
    {
        $array = [1, 2, 3, 4];
        [&$c, $d, &$e, $f] = $array;
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp73\Tests\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class ArrayDestructuringEndByValue
{
    public function run($string)
    {
        $array = [1, 2, 3, 4];
        [$c, $d, $e, $f] = $array;
        $c =& $array[0];
        $e =& $array[2];
    }
}

?>

<?php

namespace Rector\DowngradePhp73\Tests\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class ArrayDestructuringWithArrayKeysEndByRef
{
    public function run($string)
    {
        $array = ['a' => 1, 'b' => 2];
        ['a' => $a, 'b' => &$b] = $array;

        $array = [3 => 1, 5 => 2];
        [3 => $a, 5 => &$b] = $array;
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp73\Tests\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class ArrayDestructuringWithArrayKeysEndByRef
{
    public function run($string)
    {
        $array = ['a' => 1, 'b' => 2];
        ['a' => $a] = $array;
        $b =& $array['b'];

        $array = [3 => 1, 5 => 2];
        [3 => $a] = $array;
        $b =& $array[5];
    }
}

?>

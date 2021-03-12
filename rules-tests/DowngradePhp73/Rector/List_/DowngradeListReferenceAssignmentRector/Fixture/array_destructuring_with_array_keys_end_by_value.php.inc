<?php

namespace Rector\DowngradePhp73\Tests\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class ArrayDestructuringWithArrayKeysEndByValue
{
    public function run($string)
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3];
        ['a' => $a, 'b' => &$b, 'c' => $c] = $array;

        $array = [3 => 1, 5 => 2, 8 => 3];
        [3 => $a, 5 => &$b, 8 => $c] = $array;
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp73\Tests\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class ArrayDestructuringWithArrayKeysEndByValue
{
    public function run($string)
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3];
        ['a' => $a, 'b' => $b, 'c' => $c] = $array;
        $b =& $array['b'];

        $array = [3 => 1, 5 => 2, 8 => 3];
        [3 => $a, 5 => $b, 8 => $c] = $array;
        $b =& $array[5];
    }
}

?>

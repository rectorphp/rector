<?php

namespace Rector\DowngradePhp73\Tests\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class WithArrayKeysEndByValue
{
    public function run($string)
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3];
        list('a' => $a, 'b' => &$b, 'c' => $c) = $array;

        $array = [0 => 1, 3 => 2, 7 => 3];
        list(0 => $a, 3 => &$b, 7 => $c) = $array;
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp73\Tests\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class WithArrayKeysEndByValue
{
    public function run($string)
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3];
        list('a' => $a, 'b' => $b, 'c' => $c) = $array;
        $b =& $array['b'];

        $array = [0 => 1, 3 => 2, 7 => 3];
        list(0 => $a, 3 => $b, 7 => $c) = $array;
        $b =& $array[3];
    }
}

?>

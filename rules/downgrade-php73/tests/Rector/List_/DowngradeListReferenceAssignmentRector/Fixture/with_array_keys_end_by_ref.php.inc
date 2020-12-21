<?php

namespace Rector\DowngradePhp73\Tests\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class WithArrayKeysEndByRef
{
    public function run($string)
    {
        $array = ['a' => 1, 'b' => 2];
        list('a' => $a, 'b' => &$b) = $array;

        $array = [0 => 1, 3 => 2];
        list(0 => $a, 3 => &$b) = $array;
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp73\Tests\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class WithArrayKeysEndByRef
{
    public function run($string)
    {
        $array = ['a' => 1, 'b' => 2];
        list('a' => $a) = $array;
        $b =& $array['b'];

        $array = [0 => 1, 3 => 2];
        list(0 => $a) = $array;
        $b =& $array[3];
    }
}

?>

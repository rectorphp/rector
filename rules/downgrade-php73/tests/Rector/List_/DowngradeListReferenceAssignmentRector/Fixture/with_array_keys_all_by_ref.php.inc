<?php

namespace Rector\DowngradePhp73\Tests\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class WithArrayKeysAllByRef
{
    public function run($string)
    {
        $array = ['a' => 1, 'b' => 2];
        list('a' => &$a, 'b' => &$b) = $array;

        $array = [0 => 1, 3 => 2];
        list(0 => &$a, 3 => &$b) = $array;
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp73\Tests\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class WithArrayKeysAllByRef
{
    public function run($string)
    {
        $array = ['a' => 1, 'b' => 2];
        $a =& $array['a'];
        $b =& $array['b'];

        $array = [0 => 1, 3 => 2];
        $a =& $array[0];
        $b =& $array[3];
    }
}

?>

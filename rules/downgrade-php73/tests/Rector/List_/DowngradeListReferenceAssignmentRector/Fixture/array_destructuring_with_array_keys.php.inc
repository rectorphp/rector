<?php

namespace Rector\DowngradePhp73\Tests\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class ArrayDestructuringWithArrayKeysClass
{
    public function run($string)
    {
        $array = ['a' => 1, 'b' => 2];
        ['a' => $a, 'b' => &$b] = $array;
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp73\Tests\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class ArrayDestructuringWithArrayKeysClass
{
    public function run($string)
    {
        $array = ['a' => 1, 'b' => 2];
        ['a' => $a, 'b' => $b] = $array;
        $b =& $array['b'];
    }
}

?>

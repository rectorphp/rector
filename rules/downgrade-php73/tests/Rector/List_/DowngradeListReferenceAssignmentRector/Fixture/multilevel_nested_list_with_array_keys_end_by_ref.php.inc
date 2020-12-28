<?php

namespace Rector\DowngradePhp73\Tests\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class MultilevelNestedListWithArrayKeysEndByRef
{
    public function run($string)
    {
        $array = ['a' => 1, 'b' => 2, 'firstList' => ['&c' => 3, 'd' => 4, 'secondList' => ['e' => 5, 'f' => 6, 'thirdList' => ['g' => 7, '&h' => 8, 'i' => 9]]], '&j' => 10];
        list('a' => $a, 'b' => $b, 'firstList' => list('&c' => &$c, 'd' => $d, 'secondList' => list('e' => $e, 'f' => $f, 'thirdList' => list('g' => $g, '&h' => &$h, 'i' => $i))), '&j' => &$j) = $array;
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp73\Tests\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class MultilevelNestedListWithArrayKeysEndByRef
{
    public function run($string)
    {
        $array = ['a' => 1, 'b' => 2, 'firstList' => ['&c' => 3, 'd' => 4, 'secondList' => ['e' => 5, 'f' => 6, 'thirdList' => ['g' => 7, '&h' => 8, 'i' => 9]]], '&j' => 10];
        list('a' => $a, 'b' => $b, 'firstList' => list('&c' => $c, 'd' => $d, 'secondList' => list('e' => $e, 'f' => $f, 'thirdList' => list('g' => $g, '&h' => $h, 'i' => $i)))) = $array;
        $c =& $array['firstList']['&c'];
        $h =& $array['firstList']['secondList']['thirdList']['&h'];
        $j =& $array['&j'];
    }
}

?>

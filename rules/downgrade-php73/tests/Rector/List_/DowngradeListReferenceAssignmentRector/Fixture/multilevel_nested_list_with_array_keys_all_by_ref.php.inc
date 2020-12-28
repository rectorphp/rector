<?php

namespace Rector\DowngradePhp73\Tests\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class MultilevelNestedListWithArrayKeysAllByRef
{
    public function run($string)
    {
        $array = ['&a' => 1, '&b' => 2, 'firstList' => ['&c' => 3, '&d' => 4, 'secondList' => ['&e' => 5, '&f' => 6, 'thirdList' => ['&g' => 7, '&h' => 8, '&i' => 9]]]];
        list('&a' => &$a, '&b' => &$b, 'firstList' => list('&c' => &$c, '&d' => &$d, 'secondList' => list('&e' => &$e, '&f' => &$f, 'thirdList' => list('&g' => &$g, '&h' => &$h, '&i' => &$i)))) = $array;
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp73\Tests\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class MultilevelNestedListWithArrayKeysAllByRef
{
    public function run($string)
    {
        $array = ['&a' => 1, '&b' => 2, 'firstList' => ['&c' => 3, '&d' => 4, 'secondList' => ['&e' => 5, '&f' => 6, 'thirdList' => ['&g' => 7, '&h' => 8, '&i' => 9]]]];
        $a =& $array['&a'];
        $b =& $array['&b'];
        $c =& $array['firstList']['&c'];
        $d =& $array['firstList']['&d'];
        $e =& $array['firstList']['secondList']['&e'];
        $f =& $array['firstList']['secondList']['&f'];
        $g =& $array['firstList']['secondList']['thirdList']['&g'];
        $h =& $array['firstList']['secondList']['thirdList']['&h'];
        $i =& $array['firstList']['secondList']['thirdList']['&i'];
    }
}

?>

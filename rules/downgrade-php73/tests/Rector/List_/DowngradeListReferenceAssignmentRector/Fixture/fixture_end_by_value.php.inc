<?php

namespace Rector\DowngradePhp73\Tests\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class FixtureEndByValueClass
{
    public function run($string)
    {
        $array = [1, 2, 3];
        list($a, &$b, $c) = $array;
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp73\Tests\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class FixtureEndByValueClass
{
    public function run($string)
    {
        $array = [1, 2, 3];
        list($a, $b, $c) = $array;
        $b =& $array[1];
    }
}

?>

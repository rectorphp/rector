<?php

namespace Rector\Tests\DowngradePhp73\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class FixtureEndByValue
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

namespace Rector\Tests\DowngradePhp73\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class FixtureEndByValue
{
    public function run($string)
    {
        $array = [1, 2, 3];
        list($a, $b, $c) = $array;
        $b =& $array[1];
    }
}

?>

<?php

namespace Rector\DowngradePhp73\Tests\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class FixtureEndByRef
{
    public function run($string)
    {
        $array = [1, 2];
        list($a, &$b) = $array;
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp73\Tests\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class FixtureEndByRef
{
    public function run($string)
    {
        $array = [1, 2];
        list($a) = $array;
        $b =& $array[1];
    }
}

?>

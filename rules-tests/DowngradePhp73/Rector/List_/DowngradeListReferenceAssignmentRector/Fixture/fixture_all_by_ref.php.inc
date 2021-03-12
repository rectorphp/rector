<?php

namespace Rector\DowngradePhp73\Tests\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class FixtureAllByRef
{
    public function run($string)
    {
        $array = [1, 2];
        list(&$a, &$b) = $array;
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp73\Tests\Rector\List_\DowngradeListReferenceAssignmentRector\Fixture;

class FixtureAllByRef
{
    public function run($string)
    {
        $array = [1, 2];
        $a =& $array[0];
        $b =& $array[1];
    }
}

?>

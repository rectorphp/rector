<?php

namespace Rector\Php72\Tests\Rector\Assign\ReplaceEachAssignmentWithKeyCurrentRector\Fixture;

final class OldAssignment
{
    public function run()
    {
        $array = ['a' => 1, 'b' => 2];

        $old = each($array);
    }
}

?>
-----
<?php

namespace Rector\Php72\Tests\Rector\Assign\ReplaceEachAssignmentWithKeyCurrentRector\Fixture;

final class OldAssignment
{
    public function run()
    {
        $array = ['a' => 1, 'b' => 2];
        $old[1] = current($array);
        $old['value'] = current($array);
        $old[0] = key($array);
        $old['key'] = key($array);
        next($array);
    }
}

?>

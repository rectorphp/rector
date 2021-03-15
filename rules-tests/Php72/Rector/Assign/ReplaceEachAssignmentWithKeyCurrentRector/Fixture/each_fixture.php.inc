<?php

namespace Rector\Tests\Php72\Rector\Assign\ReplaceEachAssignmentWithKeyCurrentRector\Fixture;

class EachFixture
{
    public function run()
    {
        $array = ['b' => 1, 'a' => 2];

        $old = each($array);

        return $old;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php72\Rector\Assign\ReplaceEachAssignmentWithKeyCurrentRector\Fixture;

class EachFixture
{
    public function run()
    {
        $array = ['b' => 1, 'a' => 2];
        $old[1] = current($array);
        $old['value'] = current($array);
        $old[0] = key($array);
        $old['key'] = key($array);
        next($array);

        return $old;
    }
}

?>

<?php

namespace Rector\Php72\Tests\Rector\Assign\ReplaceEachAssignmentWithKeyCurrentRector\Fixture;

use Rector\Testing\Contract\RunnableInterface;

class EachFixture implements RunnableInterface
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

namespace Rector\Php72\Tests\Rector\Assign\ReplaceEachAssignmentWithKeyCurrentRector\Fixture;

use Rector\Testing\Contract\RunnableInterface;

class EachFixture implements RunnableInterface
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

<?php

namespace Rector\Php72\Tests\Rector\Assign\ReplaceEachAssignmentWithKeyCurrentRector\Fixture;

use Rector\Testing\Contract\RunnableInterface;

final class ComparedResults implements RunnableInterface
{
    public function run()
    {
        $array = ['a' => 1, 'b' => 2];

        $first = each($array);
        $second = each($array);

        return [$first, $second];
    }
}

?>
-----
<?php

namespace Rector\Php72\Tests\Rector\Assign\ReplaceEachAssignmentWithKeyCurrentRector\Fixture;

use Rector\Testing\Contract\RunnableInterface;

final class ComparedResults implements RunnableInterface
{
    public function run()
    {
        $array = ['a' => 1, 'b' => 2];
        $first[1] = current($array);
        $first['value'] = current($array);
        $first[0] = key($array);
        $first['key'] = key($array);
        next($array);
        $second[1] = current($array);
        $second['value'] = current($array);
        $second[0] = key($array);
        $second['key'] = key($array);
        next($array);

        return [$first, $second];
    }
}

?>

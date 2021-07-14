<?php

namespace Rector\Tests\Php71\Rector\Assign\AssignArrayToStringRector\Fixture;

final class InForeachAssignVariable
{
    public function fun()
    {
        $array = [1, 2, 3];

        $c = '';
        foreach ($array as $item) {
            $c[] = $item;
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\Php71\Rector\Assign\AssignArrayToStringRector\Fixture;

final class InForeachAssignVariable
{
    public function fun()
    {
        $array = [1, 2, 3];

        $c = [];
        foreach ($array as $item) {
            $c[] = $item;
        }
    }
}

?>

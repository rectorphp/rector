<?php

namespace Rector\Php\Tests\Rector\Assign\AssignArrayToStringRector\Wrong;

class WithSomeInMethodExpressions
{
    public function fun()
    {
        $array = [1, 2, 3];

        $someString = '';
        foreach ($array as $item) {
            $someString[] = $item;
        }
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\Assign\AssignArrayToStringRector\Wrong;

class WithSomeInMethodExpressions
{
    public function fun()
    {
        $array = [1, 2, 3];

        $someString = [];
        foreach ($array as $item) {
            $someString[] = $item;
        }
    }
}

?>

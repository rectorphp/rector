<?php

namespace Rector\Php\Tests\Rector\Assign\AssignArrayToStringRector\Fixture;

class WithSomeInMethod
{
    public function run()
    {
        $someString = '';
        $someString[] = 1;

        $anotherString = '';
        $anotherString[1] = 1;
    }

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

namespace Rector\Php\Tests\Rector\Assign\AssignArrayToStringRector\Fixture;

class WithSomeInMethod
{
    public function run()
    {
        $someString = [];
        $someString[] = 1;

        $anotherString = '';
        $anotherString[1] = 1;
    }

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

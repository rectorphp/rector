<?php

namespace Rector\Php71\Tests\Rector\Assign\AssignArrayToStringRector\Fixture;

class Fixture4
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

        $c = '';
        foreach ($array as $item) {
            $c[] = $item;
        }
    }
}

?>
-----
<?php

namespace Rector\Php71\Tests\Rector\Assign\AssignArrayToStringRector\Fixture;

class Fixture4
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

        $c = [];
        foreach ($array as $item) {
            $c[] = $item;
        }
    }
}

?>

<?php

namespace Rector\Php71\Tests\Rector\Assign\AssignArrayToStringRector\Fixture;

class Fixture2
{
    static $someStaticString = '';

    static $anotherStaticString = '';

    public function run()
    {
        self::$someStaticString[] = 1;

        self::$anotherStaticString[1] = 1;
    }
}

?>
-----
<?php

namespace Rector\Php71\Tests\Rector\Assign\AssignArrayToStringRector\Fixture;

class Fixture2
{
    static $someStaticString = [];

    static $anotherStaticString = '';

    public function run()
    {
        self::$someStaticString[] = 1;

        self::$anotherStaticString[1] = 1;
    }
}

?>

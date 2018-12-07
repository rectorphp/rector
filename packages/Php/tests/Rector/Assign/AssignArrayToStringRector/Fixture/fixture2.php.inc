<?php

namespace Rector\Php\Tests\Rector\Assign\AssignArrayToStringRector\Fixture;

class WithSomeStaticString
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

namespace Rector\Php\Tests\Rector\Assign\AssignArrayToStringRector\Fixture;

class WithSomeStaticString
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

<?php

namespace Rector\Tests\Php71\Rector\Assign\AssignArrayToStringRector\Fixture;

final class Fixture2
{
    static $someStaticString = '';

    public function run()
    {
        self::$someStaticString[] = 1;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php71\Rector\Assign\AssignArrayToStringRector\Fixture;

final class Fixture2
{
    static $someStaticString = [];

    public function run()
    {
        self::$someStaticString[] = 1;
    }
}

?>

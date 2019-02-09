<?php

namespace Rector\Php\Tests\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector\Fixture;

class AddStaticToMethodTest
{
    private function __construct() // private constructor
    {
    }

    public function method()
    {
    }
}

class AddStaticToMethodTest2
{
    public function __construct(string $test) // constructor with parameter
    {
    }

    public function method()
    {
        echo "test2";
    }
}

AddStaticToMethodTest::method();
AddStaticToMethodTest2::method();

?>
-----
<?php

namespace Rector\Php\Tests\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector\Fixture;

class AddStaticToMethodTest
{
    private function __construct() // private constructor
    {
    }

    public static function method()
    {
    }
}

class AddStaticToMethodTest2
{
    public function __construct(string $test) // constructor with parameter
    {
    }

    public static function method()
    {
        echo "test2";
    }
}

AddStaticToMethodTest::method();
AddStaticToMethodTest2::method();

?>

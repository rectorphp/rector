<?php

namespace Rector\Tests\Php74\Rector\Function_\ReservedFnFunctionRector\Fixture;

class Fixture
{
    public function run()
    {
        function fn($value)
        {
            return $value;
        }

        fn(5);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Function_\ReservedFnFunctionRector\Fixture;

class Fixture
{
    public function run()
    {
        function f($value)
        {
            return $value;
        }

        f(5);
    }
}

?>

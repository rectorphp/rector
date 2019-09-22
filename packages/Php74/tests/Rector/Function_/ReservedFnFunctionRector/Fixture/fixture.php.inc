<?php

namespace Rector\Php\Tests\Rector\Function_\ReservedFnFunctionRector\Fixture;

class SomeClass
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

namespace Rector\Php\Tests\Rector\Function_\ReservedFnFunctionRector\Fixture;

class SomeClass
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

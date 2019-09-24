<?php

namespace Rector\Php71\Tests\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector\Fixture;

class SomeClass
{
    public function run()
    {
        $value = 5 + '';
        $value = 5.0 + 'hi';
        $value = 5 + 'Tom';

        $value = 5 * '';
        $value = 5 * 'hi';

        $name = 'Tom';
        $value = 5 * $name;
    }
}

?>
-----
<?php

namespace Rector\Php71\Tests\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector\Fixture;

class SomeClass
{
    public function run()
    {
        $value = 5 + 0;
        $value = 5.0 + 0;
        $value = 5 + 0;

        $value = 5 * 0;
        $value = 5 * 0;

        $name = 'Tom';
        $value = 5 * $name;
    }
}

?>

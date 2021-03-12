<?php

namespace Rector\Php71\Tests\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector\Fixture;

class KnownStaticString
{
    public function run(string $anyString)
    {
        $name = 'Tom';
        $value = 5 * $name;

        $numberAsString = '123';
        $value = 5 * $numberAsString;

        $value = 5 * '12';

        $value = 10 * $anyString;
    }
}

?>
-----
<?php

namespace Rector\Php71\Tests\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector\Fixture;

class KnownStaticString
{
    public function run(string $anyString)
    {
        $name = 'Tom';
        $value = 5 * 0;

        $numberAsString = '123';
        $value = 5 * $numberAsString;

        $value = 5 * '12';

        $value = 10 * $anyString;
    }
}

?>

<?php

namespace Rector\Php\Tests\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector\Fixture;

class IgnoreConcatenationDots
{
    public function run()
    {
        $string = 'this is a string to cancatenate with the number';
        $number = 10;
        $value = $string . ' ' . $number;
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector\Fixture;

class IgnoreConcatenationDots
{
    public function run()
    {
        $string = 'this is a string to cancatenate with the number';
        $number = 10;
        $value = $string . ' ' . $number;
    }
}

?>

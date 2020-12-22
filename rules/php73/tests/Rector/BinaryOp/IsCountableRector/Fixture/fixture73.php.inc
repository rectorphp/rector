<?php

namespace Rector\Php73\Tests\Rector\BinaryOp\IsCountableRector\Fixture;

class Fixture73
{
    public function go($foo)
    {
        is_array($foo) || $foo instanceof \Countable;
    }
}

?>
-----
<?php

namespace Rector\Php73\Tests\Rector\BinaryOp\IsCountableRector\Fixture;

class Fixture73
{
    public function go($foo)
    {
        is_countable($foo);
    }
}

?>

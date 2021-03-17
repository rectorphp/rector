<?php

namespace Rector\Tests\Php73\Rector\BinaryOr\IsCountableRector\Fixture;

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

namespace Rector\Tests\Php73\Rector\BinaryOr\IsCountableRector\Fixture;

class Fixture73
{
    public function go($foo)
    {
        is_countable($foo);
    }
}

?>

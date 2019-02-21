<?php

namespace Rector\Celebrity\Tests\Rector\NotEqual\CommonNotEqualRector\Fixture;

final class SomeClass
{
    public function run($one, $two)
    {
        return $one <> $two;
    }
}

?>
-----
<?php

namespace Rector\Celebrity\Tests\Rector\NotEqual\CommonNotEqualRector\Fixture;

final class SomeClass
{
    public function run($one, $two)
    {
        return $one != $two;
    }
}

?>

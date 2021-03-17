<?php

namespace Rector\Tests\CodeQuality\Rector\NotEqual\CommonNotEqualRector\Fixture;

final class Fixture
{
    public function run($one, $two)
    {
        return $one <> $two;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\NotEqual\CommonNotEqualRector\Fixture;

final class Fixture
{
    public function run($one, $two)
    {
        return $one != $two;
    }
}

?>

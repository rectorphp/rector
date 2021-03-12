<?php

namespace Rector\Tests\Php72\Rector\Unset_\UnsetCastRector\Fixture;

class UnsetExpr
{
    public function run($value)
    {
        (unset) strlen('hi');

        $value = (unset) strlen('hi');
    }
}

?>
-----
<?php

namespace Rector\Tests\Php72\Rector\Unset_\UnsetCastRector\Fixture;

class UnsetExpr
{
    public function run($value)
    {
        $value = null;
    }
}

?>

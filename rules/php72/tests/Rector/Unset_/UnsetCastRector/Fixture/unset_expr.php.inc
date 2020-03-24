<?php

namespace Rector\Php72\Tests\Rector\Unset_\UnsetCastRector\Fixture;

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

namespace Rector\Php72\Tests\Rector\Unset_\UnsetCastRector\Fixture;

class UnsetExpr
{
    public function run($value)
    {
        $value = null;
    }
}

?>

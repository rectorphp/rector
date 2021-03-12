<?php

namespace Rector\Tests\Php72\Rector\Unset_\UnsetCastRector\Fixture;

class Fixture
{
    public function run($value)
    {
        $value = (unset) $value;

        $different = (unset) $value;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php72\Rector\Unset_\UnsetCastRector\Fixture;

class Fixture
{
    public function run($value)
    {
        unset($value);

        $different = null;
    }
}

?>

<?php

namespace Rector\Tests\DeadCode\Rector\Concat\RemoveConcatAutocastRector\Fixture;

class Fixture
{
    public function run($value)
    {
        return 'hi ' . (string) $value;
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Concat\RemoveConcatAutocastRector\Fixture;

class Fixture
{
    public function run($value)
    {
        return 'hi ' . $value;
    }
}

?>

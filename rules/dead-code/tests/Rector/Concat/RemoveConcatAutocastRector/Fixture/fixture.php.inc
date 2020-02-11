<?php

namespace Rector\DeadCode\Tests\Rector\Concat\RemoveConcatAutocastRector\Fixture;

class SomeConcatingClass
{
    public function run($value)
    {
        return 'hi ' . (string) $value;
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Concat\RemoveConcatAutocastRector\Fixture;

class SomeConcatingClass
{
    public function run($value)
    {
        return 'hi ' . $value;
    }
}

?>

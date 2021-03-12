<?php

namespace Rector\Tests\Php80\Rector\Ternary\GetDebugTypeRector\Fixture;

class Fixture
{
    public function run($value)
    {
        return is_object($value) ? get_class($value) : gettype($value);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Ternary\GetDebugTypeRector\Fixture;

class Fixture
{
    public function run($value)
    {
        return get_debug_type($value);
    }
}

?>

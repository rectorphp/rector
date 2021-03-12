<?php

namespace Rector\Tests\Php73\Rector\BinaryOr\IsCountableRector\Fixture;

use Countable;

class PolyfillFunction
{
    public function run($foo)
    {
        return is_array($foo) || $foo instanceof Countable;
    }
}

// dummy
if (! function_exists('is_countable')) {
    function is_countable($args)
    {
        return true;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php73\Rector\BinaryOr\IsCountableRector\Fixture;

use Countable;

class PolyfillFunction
{
    public function run($foo)
    {
        return is_countable($foo);
    }
}

// dummy
if (! function_exists('is_countable')) {
    function is_countable($args)
    {
        return true;
    }
}

?>

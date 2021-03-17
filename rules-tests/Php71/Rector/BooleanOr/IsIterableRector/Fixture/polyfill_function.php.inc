<?php

namespace Rector\Tests\Php71\Rector\BooleanOr\IsIterableRector\Fixture;

use Traversable;

class IsIterablePolyfillFunction
{
    public function run($foo)
    {
        return is_array($foo) || $foo instanceof Traversable;
    }
}

// dummy
if (! function_exists('is_iterable')) {
    function is_iterable($args)
    {
        return true;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php71\Rector\BooleanOr\IsIterableRector\Fixture;

use Traversable;

class IsIterablePolyfillFunction
{
    public function run($foo)
    {
        return is_iterable($foo);
    }
}

// dummy
if (! function_exists('is_iterable')) {
    function is_iterable($args)
    {
        return true;
    }
}

?>

<?php

namespace Rector\Php71\Tests\Rector\BooleanOr\IsIterableRector\Fixture;

use Traversable;

function isIterable()
{
    is_array($foo) || $foo instanceof Traversable;

    is_string($foo) || $foo instanceof Traversable;

    is_array($foo2) || $foo instanceof Traversable;

    $foo2 || $foo instanceof Traversable;
}

?>
-----
<?php

namespace Rector\Php71\Tests\Rector\BooleanOr\IsIterableRector\Fixture;

use Traversable;

function isIterable()
{
    is_iterable($foo);

    is_string($foo) || $foo instanceof Traversable;

    is_array($foo2) || $foo instanceof Traversable;

    $foo2 || $foo instanceof Traversable;
}

?>

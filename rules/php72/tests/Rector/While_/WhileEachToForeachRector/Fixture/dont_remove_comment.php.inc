<?php

namespace Rector\Php72\Tests\Rector\While_\WhileEachToForeachRector\Fixture;

function dontremovecomment()
{
    // a comment before
    while (list($key, $callback) = each($callbacks)) {
        // comment inside
    }
}

?>
-----
<?php

namespace Rector\Php72\Tests\Rector\While_\WhileEachToForeachRector\Fixture;

function dontremovecomment()
{
    // a comment before
    foreach ($callbacks as $key => $callback) {
        // comment inside
    }
}

?>

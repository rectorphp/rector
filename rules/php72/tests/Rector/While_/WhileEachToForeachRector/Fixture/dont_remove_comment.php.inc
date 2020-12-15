<?php

namespace Rector\Php72\Tests\Rector\While_\Fixture;

function dontremovecomment()
{
    // a comment
    while (list($key, $callback) = each($callbacks)) {
        // comment
    }
}

?>
-----
<?php

namespace Rector\Php72\Tests\Rector\While_\Fixture;

function dontremovecomment()
{
    // a comment
    foreach ($callbacks as $key => $callback) {
        // comment
    }
}

?>

<?php

namespace Rector\Core\Tests\Issues\JsonThrowWithChangeAndIf\Fixture;

function f()
{
    if (true && true) {
        json_decode($a);
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\JsonThrowWithChangeAndIf\Fixture;

function f()
{
    if (true && true) {
        json_decode($a, null, 512, JSON_THROW_ON_ERROR);
    }
}

?>

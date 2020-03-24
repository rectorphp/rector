<?php

namespace Rector\Php70\Tests\Rector\List_\EmptyListRector\Fixture;

function emptyList()
{
    $values = [];
    list() = $values;

    list(, ,) = $a;

    list($x, list(), $y) = $a;
}

?>
-----
<?php

namespace Rector\Php70\Tests\Rector\List_\EmptyListRector\Fixture;

function emptyList()
{
    $values = [];
    list($unusedGenerated) = $values;

    list($unusedGenerated, , ) = $a;

    list($x, list($unusedGenerated), $y) = $a;
}

?>

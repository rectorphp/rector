<?php

namespace Rector\Php\Tests\Rector\FuncCall\RemoveReferenceFromCallRector\Fixture;

function removeReference($one)
{
    return strlen(&$one);
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\FuncCall\RemoveReferenceFromCallRector\Fixture;

function removeReference($one)
{
    return strlen($one);
}

?>

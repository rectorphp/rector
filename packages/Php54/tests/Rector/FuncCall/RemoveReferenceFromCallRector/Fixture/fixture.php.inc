<?php

namespace Rector\Php54\Tests\Rector\FuncCall\RemoveReferenceFromCallRector\Fixture;

function removeReference($one)
{
    return strlen(&$one);
}

?>
-----
<?php

namespace Rector\Php54\Tests\Rector\FuncCall\RemoveReferenceFromCallRector\Fixture;

function removeReference($one)
{
    return strlen($one);
}

?>

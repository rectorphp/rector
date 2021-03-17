<?php

namespace Rector\Tests\Php54\Rector\FuncCall\RemoveReferenceFromCallRector\Fixture;

function removeReference($one)
{
    return strlen(&$one);
}

?>
-----
<?php

namespace Rector\Tests\Php54\Rector\FuncCall\RemoveReferenceFromCallRector\Fixture;

function removeReference($one)
{
    return strlen($one);
}

?>

<?php

namespace Rector\Tests\Php53\Rector\Ternary\TernaryToElvisRector\Fixture;

function elvis()
{
    $value = $a ? $a : false;
}

?>
-----
<?php

namespace Rector\Tests\Php53\Rector\Ternary\TernaryToElvisRector\Fixture;

function elvis()
{
    $value = $a ?: false;
}

?>

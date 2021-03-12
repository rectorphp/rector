<?php

namespace Rector\Php53\Tests\Rector\Ternary\TernaryToElvisRector\Fixture;

function elvis()
{
    $value = $a ? $a : false;
}

?>
-----
<?php

namespace Rector\Php53\Tests\Rector\Ternary\TernaryToElvisRector\Fixture;

function elvis()
{
    $value = $a ?: false;
}

?>

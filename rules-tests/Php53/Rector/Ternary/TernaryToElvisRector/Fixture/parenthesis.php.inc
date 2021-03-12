<?php

namespace Rector\Tests\Php53\Rector\Ternary\TernaryToElvisRector\Fixture;

function elvisRemoveParenthesis()
{
    $value = ($a - $b) ? ($a - $b) : $c;
}

?>
-----
<?php

namespace Rector\Tests\Php53\Rector\Ternary\TernaryToElvisRector\Fixture;

function elvisRemoveParenthesis()
{
    $value = $a - $b ?: $c;
}

?>

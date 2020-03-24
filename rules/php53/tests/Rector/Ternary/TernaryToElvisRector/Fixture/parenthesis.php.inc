<?php

namespace Rector\Php53\Tests\Rector\Ternary\TernaryToElvisRector\Fixture;

function elvisRemoveParenthesis()
{
    $value = ($a - $b) ? ($a - $b) : $c;
}

?>
-----
<?php

namespace Rector\Php53\Tests\Rector\Ternary\TernaryToElvisRector\Fixture;

function elvisRemoveParenthesis()
{
    $value = $a - $b ?: $c;
}

?>

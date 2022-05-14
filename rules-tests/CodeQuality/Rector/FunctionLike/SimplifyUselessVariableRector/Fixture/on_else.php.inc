<?php

namespace Rector\Tests\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector\Fixture;

function ($a) {
    if ($a) {
    } else {
        $a = 'a';
        return $a;
    }
};

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector\Fixture;

function ($a) {
    if ($a) {
    } else {
        return 'a';
    }
};

?>

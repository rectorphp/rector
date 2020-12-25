<?php

namespace Rector\CodeQualityStrict\Tests\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

function variableWithMultipleUsages()
{
    $var = 1;
    if (mktime() === false) {
        echo $var;
        return $var;
    }
}

?>
-----
<?php

namespace Rector\CodeQualityStrict\Tests\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

function variableWithMultipleUsages()
{
    if (mktime() === false) {
        $var = 1;
        echo $var;
        return $var;
    }
}

?>

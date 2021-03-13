<?php

namespace Rector\Tests\Naming\Rector\Variable\UnderscoreToCamelCaseLocalVariableNameRector\Fixture;

final class SkipFromExpressionWithCheckValueRight
{
    public function run($a_b)
    {
        if ($a_b === false) {

        }
    }
}

?>

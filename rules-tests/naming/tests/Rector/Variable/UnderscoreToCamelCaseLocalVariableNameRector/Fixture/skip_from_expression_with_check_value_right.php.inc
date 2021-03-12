<?php

namespace Rector\Naming\Tests\Rector\Variable\UnderscoreToCamelCaseLocalVariableNameRector\Fixture;

final class SkipFromExpressionWithCheckValueRight
{
    public function run($a_b)
    {
        if ($a_b === false) {

        }
    }
}

?>

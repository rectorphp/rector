<?php

namespace Rector\Tests\Privatization\Rector\ClassMethod\ChangeGlobalVariablesToPropertiesRector\Fixture;

$variable = 'value';

class SkipReadOnlyGlobal
{
    public function go()
    {
        global $variable;

        echo $variable;
    }
}

?>

<?php

namespace Rector\Tests\Privatization\Rector\Class_\ChangeGlobalVariablesToPropertiesRector\Fixture;

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

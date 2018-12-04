<?php

namespace Rector\CodeQuality\Tests\Rector\Return_\SimplifyUselessVariableRector\Wrong;

function simplifyUselessVariable4()
{
    $fixerConfigurationResolver = new FixerConfigurationResolver($fixerOptions);





    return $fixerConfigurationResolver;
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Return_\SimplifyUselessVariableRector\Wrong;

function simplifyUselessVariable4()
{
    return new FixerConfigurationResolver($fixerOptions);
}

?>

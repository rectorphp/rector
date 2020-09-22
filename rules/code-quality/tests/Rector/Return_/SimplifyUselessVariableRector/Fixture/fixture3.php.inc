<?php

namespace Rector\CodeQuality\Tests\Rector\Return_\SimplifyUselessVariableRector\Fixture;

function simplifyUselessVariable3()
{
    $fixerConfigurationResolver = new FixerConfigurationResolver($fixerOptions);







    // keep




    return $fixerConfigurationResolver;
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Return_\SimplifyUselessVariableRector\Fixture;

function simplifyUselessVariable3()
{
    // keep




    return new FixerConfigurationResolver($fixerOptions);
}

?>

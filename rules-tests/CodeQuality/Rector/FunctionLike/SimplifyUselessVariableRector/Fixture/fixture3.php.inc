<?php

namespace Rector\Tests\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector\Fixture;

function simplifyUselessVariable3()
{
    $fixerConfigurationResolver = new FixerConfigurationResolver($fixerOptions);







    // keep




    return $fixerConfigurationResolver;
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector\Fixture;

function simplifyUselessVariable3()
{
    // keep




    return new FixerConfigurationResolver($fixerOptions);
}

?>

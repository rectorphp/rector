<?php

namespace Rector\Tests\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector\Fixture;

function simplifyUselessVariable4()
{
    $fixerConfigurationResolver = new FixerConfigurationResolver($fixerOptions);





    return $fixerConfigurationResolver;
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector\Fixture;

function simplifyUselessVariable4()
{
    return new FixerConfigurationResolver($fixerOptions);
}

?>

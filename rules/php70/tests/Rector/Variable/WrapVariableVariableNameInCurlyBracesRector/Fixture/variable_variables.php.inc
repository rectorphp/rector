<?php

namespace Rector\Php70\Rector\Variable\WrapVariableVariableNameInCurlyBracesRector\Fixture;

function variable_variables($value)
{
    $$value = true;
}

?>
-----
<?php

namespace Rector\Php70\Rector\Variable\WrapVariableVariableNameInCurlyBracesRector\Fixture;

function variable_variables($value)
{
    ${$value} = true;
}

?>

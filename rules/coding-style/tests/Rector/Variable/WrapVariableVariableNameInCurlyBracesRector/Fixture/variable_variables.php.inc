<?php

namespace Rector\CodingStyle\Rector\Variable\WrapVariableVariableNameInCurlyBracesRector\Fixture;

function variable_variables($value)
{
    $$value = true;
}

?>
-----
<?php

namespace Rector\CodingStyle\Rector\Variable\WrapVariableVariableNameInCurlyBracesRector\Fixture;

function variable_variables($value)
{
    ${$value} = true;
}

?>

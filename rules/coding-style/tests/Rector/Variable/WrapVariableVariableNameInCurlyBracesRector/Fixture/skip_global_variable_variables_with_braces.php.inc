<?php

namespace Rector\CodingStyle\Rector\Variable\WrapVariableVariableNameInCurlyBracesRector\Fixture;

function skip_global_variable_variables_with_braces($foo)
{
    global ${$foo->bar};
}

<?php

namespace Rector\Php70\Rector\Variable\WrapVariableVariableNameInCurlyBracesRector\Fixture;

function skip_global_variable_variables_with_braces($foo)
{
    global ${$foo->bar};
}

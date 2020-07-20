<?php

namespace Rector\CodingStyle\Rector\Variable\WrapVariableVariableNameInCurlyBracesRector\Fixture;

function skip_variable_variables_with_braces($value)
{
    ${$value} = true;
}

<?php

namespace Rector\Tests\Php70\Rector\Variable\WrapVariableVariableNameInCurlyBracesRector\Fixture;

function skip_variable_variables_with_braces($value)
{
    ${$value} = true;
}

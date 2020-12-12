<?php

namespace Rector\Php70\Tests\Rector\Variable\WrapVariableVariableNameInCurlyBracesRector\Fixture;

function skip_variable_variables_with_braces($value)
{
    ${$value} = true;
}

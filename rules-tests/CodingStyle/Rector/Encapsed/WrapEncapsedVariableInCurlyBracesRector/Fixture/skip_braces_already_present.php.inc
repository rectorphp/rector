<?php

namespace Rector\Tests\CodingStyle\Rector\Encapsed\WrapEncapsedVariableInCurlyBracesRector\Fixture;

function skip_braces_already_present($world)
{
    return "Hello {$world}!";
}

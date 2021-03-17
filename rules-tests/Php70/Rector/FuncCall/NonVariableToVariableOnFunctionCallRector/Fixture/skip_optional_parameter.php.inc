<?php

namespace Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

function withOptionalParameter(&$a = null) {}

function optionalParameter()
{
    baz();
}

<?php

namespace Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

function withOptionalParameter(&$a = null) {}

function optionalParameter()
{
    baz();
}

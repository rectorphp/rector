<?php

namespace Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

function arrayDimFetch()
{
    reset([1][0]);
}

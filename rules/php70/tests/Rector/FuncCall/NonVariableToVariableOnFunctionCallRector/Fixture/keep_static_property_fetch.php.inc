<?php

namespace Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

function staticPropertyFetch()
{
    reset(\stdClass::$dummy);
}

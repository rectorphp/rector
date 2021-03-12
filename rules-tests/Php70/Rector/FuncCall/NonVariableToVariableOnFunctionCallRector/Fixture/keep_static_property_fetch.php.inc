<?php

namespace Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

function staticPropertyFetch()
{
    reset(\stdClass::$dummy);
}

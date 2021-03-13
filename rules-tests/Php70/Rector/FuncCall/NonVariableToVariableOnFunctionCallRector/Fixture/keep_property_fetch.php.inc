<?php

namespace Rector\Tests\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

function propertyFetch()
{
    reset((new \stdClass())->dummy);
}

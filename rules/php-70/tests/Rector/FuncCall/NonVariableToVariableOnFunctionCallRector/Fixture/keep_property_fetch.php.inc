<?php

namespace Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

function propertyFetch()
{
    reset((new \stdClass())->dummy);
}

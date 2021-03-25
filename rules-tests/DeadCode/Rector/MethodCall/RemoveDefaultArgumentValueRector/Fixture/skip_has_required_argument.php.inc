<?php

namespace Rector\Tests\DeadCode\Rector\MethodCall\RemoveDefaultArgumentValueRector\Fixture;

use stdClass;

function skipHasRequiredArgument(stdClass $stdClass)
{
}

skipHasRequiredArgument(new stdClass);
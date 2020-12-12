<?php

namespace Rector\Transform\Tests\Rector\Assign\PropertyAssignToMethodCallRector\Fixture;

use stdClass;

class SkipInvalidType
{
    public function createForm()
    {
        $control = new stdClass();
        $control->checkAllowedValues = false;
    }
}

<?php

namespace Rector\Tests\Transform\Rector\Assign\PropertyAssignToMethodCallRector\Fixture;

use stdClass;

class SkipInvaidType
{
    public function createForm()
    {
        $control = new stdClass();
        $control->checkAllowedValues = false;
    }
}

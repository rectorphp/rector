<?php

namespace Rector\Transform\Tests\Rector\Assign\PropertyAssignToMethodCallRector\Fixture;

use Rector\Transform\Tests\Rector\Assign\PropertyAssignToMethodCallRector\Source\ChoiceControl;

class Fixture
{
    public function createForm()
    {
        $control = new ChoiceControl();
        $control->checkAllowedValues = false;
    }
}

?>
-----
<?php

namespace Rector\Transform\Tests\Rector\Assign\PropertyAssignToMethodCallRector\Fixture;

use Rector\Transform\Tests\Rector\Assign\PropertyAssignToMethodCallRector\Source\ChoiceControl;

class Fixture
{
    public function createForm()
    {
        $control = new ChoiceControl();
        $control->checkDefaultValue(false);
    }
}

?>

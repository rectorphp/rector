<?php

namespace Rector\Tests\Transform\Rector\Assign\PropertyAssignToMethodCallRector\Fixture;

use Rector\Tests\Transform\Rector\Assign\PropertyAssignToMethodCallRector\Source\ChoiceControl;

class SomePresenter
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

namespace Rector\Tests\Transform\Rector\Assign\PropertyAssignToMethodCallRector\Fixture;

use Rector\Tests\Transform\Rector\Assign\PropertyAssignToMethodCallRector\Source\ChoiceControl;

class SomePresenter
{
    public function createForm()
    {
        $control = new ChoiceControl();
        $control->checkDefaultValue(false);
    }
}

?>

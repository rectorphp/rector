<?php

namespace Rector\Tests\Rector\Assign\PropertyAssignToMethodCallRector\Wrong;

use Rector\Tests\Rector\Assign\PropertyAssignToMethodCallRector\Source\MultiChoiceControl;

class SomePresenter
{
    public function createForm()
    {
        $control = new MultiChoiceControl();
        $control->checkAllowedValues = false;
    }
}

?>
-----
<?php

namespace Rector\Tests\Rector\Assign\PropertyAssignToMethodCallRector\Wrong;

use Rector\Tests\Rector\Assign\PropertyAssignToMethodCallRector\Source\MultiChoiceControl;

class SomePresenter
{
    public function createForm()
    {
        $control = new MultiChoiceControl();
        $control->checkDefaultValue(false);
    }
}

?>

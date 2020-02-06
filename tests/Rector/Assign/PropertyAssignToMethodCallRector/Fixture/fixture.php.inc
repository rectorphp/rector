<?php

namespace Rector\Core\Tests\Rector\Assign\PropertyAssignToMethodCallRector\Fixture;

use Rector\Core\Tests\Rector\Assign\PropertyAssignToMethodCallRector\Source\MultiChoiceControl;

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

namespace Rector\Core\Tests\Rector\Assign\PropertyAssignToMethodCallRector\Fixture;

use Rector\Core\Tests\Rector\Assign\PropertyAssignToMethodCallRector\Source\MultiChoiceControl;

class SomePresenter
{
    public function createForm()
    {
        $control = new MultiChoiceControl();
        $control->checkDefaultValue(false);
    }
}

?>

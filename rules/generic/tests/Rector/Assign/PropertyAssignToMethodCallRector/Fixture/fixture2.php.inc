<?php

namespace Rector\Generic\Tests\Rector\Assign\PropertyAssignToMethodCallRector\Fixture;

use Rector\Generic\Tests\Rector\Assign\PropertyAssignToMethodCallRector\Source\ChoiceControl;

class Some2Presenter
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

namespace Rector\Generic\Tests\Rector\Assign\PropertyAssignToMethodCallRector\Fixture;

use Rector\Generic\Tests\Rector\Assign\PropertyAssignToMethodCallRector\Source\ChoiceControl;

class Some2Presenter
{
    public function createForm()
    {
        $control = new ChoiceControl();
        $control->checkDefaultValue(false);
    }
}

?>

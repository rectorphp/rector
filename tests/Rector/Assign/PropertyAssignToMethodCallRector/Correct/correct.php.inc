<?php declare (strict_types=1);

use Rector\Tests\Rector\Assign\PropertyAssignToMethodCallRector\Source\MultiChoiceControl;

class SomePresenter
{
    public function createForm()
    {
        $control = new MultiChoiceControl();
        $control->checkDefaultValue(false);
    }
}

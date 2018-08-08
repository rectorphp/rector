<?php declare (strict_types=1);

use Rector\Tests\Rector\Assign\PropertyAssignToMethodCallRector\Source\ChoiceControl;

class SomePresenter
{
    public function createForm()
    {
        $control = new ChoiceControl();
        $control->checkAllowedValues = false;
    }
}

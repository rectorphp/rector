<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;

final class ParamType
{
    public function run(SomeForm $someForm)
    {
        $someForm['items']->getValues();
    }
}

final class SomeForm extends Form
{
    public function __construct()
    {
        $this->addRadioList('items');
    }
}

?>
-----
<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;

final class ParamType
{
    public function run(SomeForm $someForm)
    {
        /** @var \Nette\Forms\Controls\RadioList $itemsControl */
        $itemsControl = $someForm['items'];
        $itemsControl->getValues();
    }
}

final class SomeForm extends Form
{
    public function __construct()
    {
        $this->addRadioList('items');
    }
}

?>

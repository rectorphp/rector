<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;

class SelectBoxPresenter
{
    public function run()
    {
        $form = new Form();
        $form->addSelect('items', 'Email');
        $form['items']->value = 'hey@hi.hello';
    }
}

?>
-----
<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;

class SelectBoxPresenter
{
    public function run()
    {
        $form = new Form();
        $form->addSelect('items', 'Email');
        /** @var \Nette\Forms\Controls\SelectBox $itemsControl */
        $itemsControl = $form['items'];
        $itemsControl->value = 'hey@hi.hello';
    }
}

?>

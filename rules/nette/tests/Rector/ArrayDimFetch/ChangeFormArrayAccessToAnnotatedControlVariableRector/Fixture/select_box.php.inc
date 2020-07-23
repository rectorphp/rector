<?php

namespace Rector\Nette\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;

class SelectBoxPresenter
{
    public function run()
    {
        $form = new Form();
        $this->addText('email', 'Email');
        $form['email']->value = 'hey@hi.hello';
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;

class SelectBoxPresenter
{
    public function run()
    {
        $form = new Form();
        $this->addText('email', 'Email');
        /** @var \Nette\Forms\Controls\BaseControl $emailControl */
        $emailControl = $form['email'];
        $emailControl->value = 'hey@hi.hello';
    }
}

?>

<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;

class FormBuilderInAnotherCall
{
    public function run()
    {
        $form = $this->makeForm();
        $form['email_else']->value = 'hey@hi.hello';
    }

    public function makeForm(): Form
    {
        $form = new Form();
        $form->addText('email_else', 'Email');

        return $form;
    }
}

?>
-----
<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;

class FormBuilderInAnotherCall
{
    public function run()
    {
        $form = $this->makeForm();
        /** @var \Nette\Forms\Controls\TextInput $emailElseControl */
        $emailElseControl = $form['email_else'];
        $emailElseControl->value = 'hey@hi.hello';
    }

    public function makeForm(): Form
    {
        $form = new Form();
        $form->addText('email_else', 'Email');

        return $form;
    }
}

?>

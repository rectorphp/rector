<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;

class FormWithAddRule
{
    public function run()
    {
        $form = $this->makeForm();
        $form['text']->value = 'hey@hi.hello';
    }

    public function makeForm(): Form
    {
        $form = new Form();
        $form->addTextArea('text', 'Text')
            ->addRule([$this, 'check']);

        return $form;
    }
}

?>
-----
<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;

class FormWithAddRule
{
    public function run()
    {
        $form = $this->makeForm();
        /** @var \Nette\Forms\Controls\TextArea $textControl */
        $textControl = $form['text'];
        $textControl->value = 'hey@hi.hello';
    }

    public function makeForm(): Form
    {
        $form = new Form();
        $form->addTextArea('text', 'Text')
            ->addRule([$this, 'check']);

        return $form;
    }
}

?>

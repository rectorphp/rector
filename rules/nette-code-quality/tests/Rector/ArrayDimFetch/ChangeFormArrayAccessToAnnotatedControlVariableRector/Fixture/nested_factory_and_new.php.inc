<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;

class NestedFactoryAndNew
{
    public function run()
    {
        $form = $this->makeForm();
        $form['title']->value = 'foo';

        $form['another_title']->value = 'bar';
    }

    public function makeForm(): Form
    {
        $form = new FormWithTitleLocal();
        $form->addText('another_title');

        return $form;
    }
}

class FormWithTitleLocal extends Form
{
    public function __construct()
    {
        $this->addText('title');
    }
}

?>
-----
<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;

class NestedFactoryAndNew
{
    public function run()
    {
        $form = $this->makeForm();
        /** @var \Nette\Forms\Controls\TextInput $titleControl */
        $titleControl = $form['title'];
        $titleControl->value = 'foo';
        /** @var \Nette\Forms\Controls\TextInput $anotherTitleControl */
        $anotherTitleControl = $form['another_title'];

        $anotherTitleControl->value = 'bar';
    }

    public function makeForm(): Form
    {
        $form = new FormWithTitleLocal();
        $form->addText('another_title');

        return $form;
    }
}

class FormWithTitleLocal extends Form
{
    public function __construct()
    {
        $this->addText('title');
    }
}

?>

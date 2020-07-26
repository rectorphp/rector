<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;

class NestedFactory
{
    /**
     * @var FormWithTitleFactory
     */
    private $formWithTitleFactory;

    public function __construct(FormWithTitleFactory $formWithTitleFactory)
    {
        $this->formWithTitleFactory = $formWithTitleFactory;
    }

    public function run()
    {
        $form = $this->makeForm();
        $form['title']->value = 'foo';

        $form['another_title']->value = 'bar';
    }

    public function makeForm(): Form
    {
        $form = $this->formWithTitleFactory->create();
        $form->addText('another_title');

        return $form;
    }
}

interface FormWithTitleFactory
{
    public function create(): FormWithTitle;
}

class FormWithTitle extends Form
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

class NestedFactory
{
    /**
     * @var FormWithTitleFactory
     */
    private $formWithTitleFactory;

    public function __construct(FormWithTitleFactory $formWithTitleFactory)
    {
        $this->formWithTitleFactory = $formWithTitleFactory;
    }

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
        $form = $this->formWithTitleFactory->create();
        $form->addText('another_title');

        return $form;
    }
}

interface FormWithTitleFactory
{
    public function create(): FormWithTitle;
}

class FormWithTitle extends Form
{
    public function __construct()
    {
        $this->addText('title');
    }
}

?>

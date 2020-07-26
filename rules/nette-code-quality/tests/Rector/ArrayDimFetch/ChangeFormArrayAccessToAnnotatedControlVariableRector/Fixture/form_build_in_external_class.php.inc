<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;

class FormBuilderInExternalClass
{
    /**
     * @var SomeFormFactory
     */
    private $someFormFactory;

    public function __construct(SomeFormFactory $someFormFactory)
    {
        $this->someFormFactory = $someFormFactory;
    }

    public function run()
    {
        $form = $this->someFormFactory->createForm();
        $form['items']->value = 'hey@hi.hello';
    }
}

class SomeFormFactory
{
    public function createForm(): Form
    {
        $form = new Form();
        $form->addSelect('items');

        return $form;
    }
}

?>
-----
<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;

class FormBuilderInExternalClass
{
    /**
     * @var SomeFormFactory
     */
    private $someFormFactory;

    public function __construct(SomeFormFactory $someFormFactory)
    {
        $this->someFormFactory = $someFormFactory;
    }

    public function run()
    {
        $form = $this->someFormFactory->createForm();
        /** @var \Nette\Forms\Controls\SelectBox $itemsControl */
        $itemsControl = $form['items'];
        $itemsControl->value = 'hey@hi.hello';
    }
}

class SomeFormFactory
{
    public function createForm(): Form
    {
        $form = new Form();
        $form->addSelect('items');

        return $form;
    }
}

?>

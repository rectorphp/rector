<?php

namespace Rector\Tests\NetteCodeQuality\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Rector\Tests\NetteCodeQuality\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Source\SomeFormFactory;

final class FormBuilderInExternalClass
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

?>
-----
<?php

namespace Rector\Tests\NetteCodeQuality\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Rector\Tests\NetteCodeQuality\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Source\SomeFormFactory;

final class FormBuilderInExternalClass
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

?>

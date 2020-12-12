<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;

class SomePresenter
{
    public function run()
    {
        $form = new Form();
        $form->addText('email', 'Email');
        $form['email']->value = 'hey@hi.hello';
    }
}

?>
-----
<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;

class SomePresenter
{
    public function run()
    {
        $form = new Form();
        $form->addText('email', 'Email');
        /** @var \Nette\Forms\Controls\TextInput $emailControl */
        $emailControl = $form['email'];
        $emailControl->value = 'hey@hi.hello';
    }
}

?>

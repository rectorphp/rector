<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

class FormViaGetComponentInMethod extends Control
{
    public function run()
    {
        $form = $this->getComponent('some');
        $form['title_inner']->value = 'wohoooo';
    }

    public function createComponentSome(): ExternalFormWithTitleInMethod
    {
        $form = new ExternalFormWithTitleInMethod();
        $form->addText('title_inner');
        return $form;
    }
}

final class ExternalFormWithTitleInMethod extends Form
{
}

?>
-----
<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

class FormViaGetComponentInMethod extends Control
{
    public function run()
    {
        $form = $this->getComponent('some');
        /** @var \Nette\Forms\Controls\TextInput $titleInnerControl */
        $titleInnerControl = $form['title_inner'];
        $titleInnerControl->value = 'wohoooo';
    }

    public function createComponentSome(): ExternalFormWithTitleInMethod
    {
        $form = new ExternalFormWithTitleInMethod();
        $form->addText('title_inner');
        return $form;
    }
}

final class ExternalFormWithTitleInMethod extends Form
{
}

?>

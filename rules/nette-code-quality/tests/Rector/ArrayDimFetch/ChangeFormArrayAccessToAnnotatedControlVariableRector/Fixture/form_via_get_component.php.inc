<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

class FormViaGetComponent extends Control
{
    public function run()
    {
        $form = $this->getComponent('some');
        $form['title']->value = 'wohoooo';
    }

    public function createComponentSome(): ExternalFormWithTitle
    {
        return new ExternalFormWithTitle();
    }
}

final class ExternalFormWithTitle extends Form
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

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

class FormViaGetComponent extends Control
{
    public function run()
    {
        $form = $this->getComponent('some');
        /** @var \Nette\Forms\Controls\TextInput $titleControl */
        $titleControl = $form['title'];
        $titleControl->value = 'wohoooo';
    }

    public function createComponentSome(): ExternalFormWithTitle
    {
        return new ExternalFormWithTitle();
    }
}

final class ExternalFormWithTitle extends Form
{
    public function __construct()
    {
        $this->addText('title');
    }
}

?>

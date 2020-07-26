<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;

class FormBuilderInNewInstanceElseWhere
{
    public function run()
    {
        $form = new LocalForm();
        $form['unique_name']->value = 'hey@hi.hello';
    }
}

class LocalForm extends Form
{
    public function __construct()
    {
        $this->addText('unique_name');
    }
}

?>
-----
<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;

class FormBuilderInNewInstanceElseWhere
{
    public function run()
    {
        $form = new LocalForm();
        /** @var \Nette\Forms\Controls\TextInput $uniqueNameControl */
        $uniqueNameControl = $form['unique_name'];
        $uniqueNameControl->value = 'hey@hi.hello';
    }
}

class LocalForm extends Form
{
    public function __construct()
    {
        $this->addText('unique_name');
    }
}

?>

<?php

namespace Rector\Tests\NetteCodeQuality\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;
use Rector\Tests\NetteCodeQuality\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Source\Multiplier;

final class NestedMultiplier
{
    public function run()
    {
        return new Multiplier(function () {
            $form = $this->create();
            $form['name']->getControlPrototype()
                ->addAttributes(['class' => 'first']);
        });
    }

    public function create(): Form
    {
        $form = new Form();
        $form->addText('name');
        return $form;
    }
}

?>
-----
<?php

namespace Rector\Tests\NetteCodeQuality\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;
use Rector\Tests\NetteCodeQuality\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Source\Multiplier;

final class NestedMultiplier
{
    public function run()
    {
        return new Multiplier(function () {
            $form = $this->create();
            /** @var \Nette\Forms\Controls\TextInput $nameControl */
            $nameControl = $form['name'];
            $nameControl->getControlPrototype()
                ->addAttributes(['class' => 'first']);
        });
    }

    public function create(): Form
    {
        $form = new Form();
        $form->addText('name');
        return $form;
    }
}

?>

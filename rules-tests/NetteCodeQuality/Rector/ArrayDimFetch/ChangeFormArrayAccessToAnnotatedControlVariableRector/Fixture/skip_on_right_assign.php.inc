<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;

class SkipOnRightAssign
{
    public function run()
    {
        $form = new Form();
        $form->addText('email', 'Email');

        /** @var \Nette\Forms\Controls\BaseControl $emailControl */
        $emailControl = $form['email'];
    }
}

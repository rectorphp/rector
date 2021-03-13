<?php

namespace Rector\Tests\NetteCodeQuality\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;

class SkipInIssetUnset
{
    public function run()
    {
        $form = new Form();
        $form->addText('email', 'Email');
        if (isset($form['email'])) {
            unset($form['email']);
        }
    }
}

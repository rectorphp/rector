<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;
use Nette\Forms\Controls\TextInput;

class SkipAssigned
{
    public function run(Form $form)
    {
        $form['email_else'] = new TextInput('label');
    }
}

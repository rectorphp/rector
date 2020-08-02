<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;

class SkipUnset
{
    public function run(Form $form, $value)
    {
        if ($value) {
            unset($form['email']);
        }
    }
}

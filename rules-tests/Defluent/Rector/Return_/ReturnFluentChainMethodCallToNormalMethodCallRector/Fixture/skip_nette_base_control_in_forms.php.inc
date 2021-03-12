<?php

namespace Rector\Defluent\Tests\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector\Fixture;

use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;

class SkipNetteBaseControlInForms
{
    public function run()
    {
        $form = new Form();
        /** @var TextInput $someControl */
        $someControl = $form->addText('textInput');
        $someControl->setRequired(1)->addRule(1);
    }
}

<?php declare (strict_types=1);

use Nette\Application\UI\Form;

class SomePresenter
{
    public function createNetteForm()
    {
        $form = new Form;

        $form->addRule(~Form::FILLED, '...');
        $form->addRule(~Form::EQUAL, '...');

        return $form;
    }
}
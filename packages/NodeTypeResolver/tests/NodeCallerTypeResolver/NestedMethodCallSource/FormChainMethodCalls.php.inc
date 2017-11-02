<?php declare (strict_types=1);

class SomePresenter
{
    public function createNetteForm()
    {
        $form = new \Nette\Application\UI\Form;
        $form->addText('name')
            ->addCondition($form::FILLED)
//            ->addRule('...')
            ->addRule('...');

        return $form;
    }
}

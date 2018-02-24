<?php declare (strict_types=1);

use Nette\Application\UI\Form;

class SomePresenter
{
    public function createNetteForm()
    {
        $form = new Form;

        $form->addRule(Form::NOT_FILLED, '...');
        $form->addRule(Form::NOT_EQUAL, '...');

        return $form;
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}
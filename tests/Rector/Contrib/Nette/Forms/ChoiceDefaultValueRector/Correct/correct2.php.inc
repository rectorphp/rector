<?php declare (strict_types=1);

class SomePresenter
{
    public function createForm()
    {
        $control = new Nette\Forms\Controls\ChoiceControl;
        $control->checkDefaultValue(false);
    }
}
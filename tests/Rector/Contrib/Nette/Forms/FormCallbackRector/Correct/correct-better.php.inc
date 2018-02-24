<?php declare (strict_types=1);

class SomePresenter
{
    public function createForm()
    {
        $form = new Form;

        $form->onSuccess[] = function (Form $form) {
            $this->someMethod($form);
        };

        return $form;
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}
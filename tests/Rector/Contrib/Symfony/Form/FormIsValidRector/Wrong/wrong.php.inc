<?php declare (strict_types=1);

class SomeController
{
    public function action()
    {
        $form = new \Symfony\Component\Form\Form;

        if ($form->isValid()) {
            $this->processForm($form);
        }
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}
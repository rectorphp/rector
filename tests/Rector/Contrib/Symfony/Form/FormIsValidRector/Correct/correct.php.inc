<?php declare (strict_types=1);

class SomeController
{
    public function action()
    {
        $form = new \Symfony\Component\Form\Form;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->processForm($form);
        }
    }
}
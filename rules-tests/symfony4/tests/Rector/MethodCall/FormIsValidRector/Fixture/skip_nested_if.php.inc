<?php

namespace Rector\Symfony4\Tests\Rector\MethodCall\FormIsValidRector\Fixture;

use Symfony\Component\Form\Form;

class SkipNestedIfsController
{
    public function action()
    {
        $form = new Form();

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->processForm($form);
            }
            $this->doSomethingMore();
        }
    }
}

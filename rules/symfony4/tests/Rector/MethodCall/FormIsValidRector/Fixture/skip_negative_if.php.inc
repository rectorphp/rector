<?php

namespace Rector\Symfony4\Tests\Rector\MethodCall\FormIsValidRector\Fixture;

use Symfony\Component\Form\Form;

class SkipNegativeIf
{
    public function action()
    {
        $form = new Form();

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->doSomething();
        }
    }
}

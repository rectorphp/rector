<?php declare (strict_types=1);

namespace Rector\Tests\FormIsValidRector;

use Rector\Symfony\Tests\Rector\Form\FormIsValidRector\Source\Form;

class SomeController
{
    public function action()
    {
        $form = new Form();

        if ($form->isSubmitted() && $form->isValid()) {
            $this->processForm($form);
        }
    }
}

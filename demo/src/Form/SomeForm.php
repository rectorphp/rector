<?php declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\FormBuilder;

final class SomeForm
{
    public function build()
    {
        $formBuilder = new FormBuilder;
        $formBuilder->add('task', 'form.type.text');

        // not just a string, but specific type
        $variable = 'form.type.text';

        return $formBuilder;
    }
}

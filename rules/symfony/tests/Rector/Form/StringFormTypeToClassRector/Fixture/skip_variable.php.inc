<?php

namespace Rector\Symfony\Tests\Rector\Form\StringFormTypeToClassRector\Fixture;

use Symfony\Component\Form\FormBuilder;

final class SkipVariable
{
    public function action()
    {
        $variable = 'form.type.text';
    }
}

<?php

namespace Rector\Symfony\Tests\Rector\MethodCall\StringFormTypeToClassRector\Fixture;

use Symfony\Component\Form\FormBuilder;

final class SkipVariable
{
    public function action()
    {
        $variable = 'form.type.text';
    }
}

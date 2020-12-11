<?php

namespace Rector\Symfony3\Tests\Rector\MethodCall\StringFormTypeToClassRector\Fixture;

use Symfony\Component\Form\FormBuilder;

final class SkipVariable
{
    public function action()
    {
        $variable = 'form.type.text';
    }
}

<?php

namespace Rector\TypeDeclaration\Tests\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Fixture;

use Nette\Application\UI\Form;

class SkipNetteFormClassCall
{
    public function create()
    {
        $form = new Form;

        $form->addText('reading', 'Reading')
            ->setRequired('...')
            ->getControlPrototype()
            ->nonExistingMagic('rating');

        return $form;
    }
}

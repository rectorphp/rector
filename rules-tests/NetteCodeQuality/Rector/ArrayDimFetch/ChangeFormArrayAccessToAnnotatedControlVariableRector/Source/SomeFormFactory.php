<?php

declare(strict_types=1);

namespace Rector\Tests\NetteCodeQuality\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Source;

use Nette\Application\UI\Form;

final class SomeFormFactory
{
    public function createForm(): Form
    {
        $form = new Form();
        $form->addSelect('items');

        return $form;
    }
}

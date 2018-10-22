<?php

namespace Rector\Symfony\Tests\Rector\MethodCall\CascadeValidationFormBuilderRector\Wrong;

class SomeController
{
    public function someMethod()
    {
        $form = $this->createFormBuilder($article, ['cascade_validation' => true])
            ->add('author', new AuthorType())
            ->getForm();

        $form = $this->createFormBuilder($article, ['cascade_validation' => true])
            ->add('author', new AuthorType())
            ->add('name', new TextType(), [
                'someOption' => 'value'
            ])
            ->getForm();
    }

    protected function createFormBuilder()
    {
        return new FormBuilder();
    }
}

<?php

namespace Rector\Symfony\Tests\Rector\MethodCall\CascadeValidationFormBuilderRector\Wrong;

class SomeController
{
    public function someMethod()
    {
        $form = $this->createFormBuilder($article)
            ->add('author', new AuthorType(), ['constraints' => new \Symfony\Component\Validator\Constraints\Valid()])
            ->getForm();

        $form = $this->createFormBuilder($article)
            ->add('author', new AuthorType(), ['constraints' => new \Symfony\Component\Validator\Constraints\Valid()])
            ->add('name', new TextType(), [
                'someOption' => 'value', 'constraints' => new \Symfony\Component\Validator\Constraints\Valid()
            ])
            ->getForm();
    }

    protected function createFormBuilder()
    {
        return new FormBuilder();
    }
}

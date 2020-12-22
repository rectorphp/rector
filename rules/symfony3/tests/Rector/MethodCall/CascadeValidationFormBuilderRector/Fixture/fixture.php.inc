<?php

namespace Rector\Symfony3\Tests\Rector\MethodCall\CascadeValidationFormBuilderRector\Fixture;

class Fixture
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

?>
-----
<?php

namespace Rector\Symfony3\Tests\Rector\MethodCall\CascadeValidationFormBuilderRector\Fixture;

class Fixture
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

?>

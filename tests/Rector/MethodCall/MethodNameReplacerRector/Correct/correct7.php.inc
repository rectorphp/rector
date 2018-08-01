<?php declare (strict_types=1);

class MyType extends \Symfony\Component\Form\AbstractType
{
    public function configureOptions(SomeResolver $resolver)
    {
        $something = new Something();
        $something->setDefaultOptions();

        parent::configureOptions($resolver);
    }
}

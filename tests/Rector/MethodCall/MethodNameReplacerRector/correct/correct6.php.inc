<?php declare (strict_types=1);

class MyType extends \Symfony\Component\Form\AbstractType
{
    public function configureOptions(SomeResolver $resolver)
    {
        parent::configureOptions($resolver);
    }
}

class AnotherClass
{
    public function setDefaultOptions(SomeResolver $resolver)
    {
        parent::setDefaultOptions($resolver);
    }
}

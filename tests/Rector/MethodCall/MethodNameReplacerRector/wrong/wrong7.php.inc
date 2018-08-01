<?php declare (strict_types=1);

class MyType extends \Symfony\Component\Form\AbstractType
{
    public function setDefaultOptions(SomeResolver $resolver)
    {
        $something = new Something();
        $something->setDefaultOptions();

        parent::setDefaultOptions($resolver);
    }
}

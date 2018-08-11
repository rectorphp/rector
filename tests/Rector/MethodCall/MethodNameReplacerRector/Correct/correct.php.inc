<?php declare (strict_types=1);

use Rector\Tests\Rector\MethodCall\MethodNameReplacerRector\Source\AbstractType;

class MyCustomType extends AbstractType
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

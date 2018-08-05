<?php declare (strict_types=1);

use Rector\Tests\Rector\MethodCall\MethodNameReplacerRector\Source\AbstractType;

class MyType extends AbstractType
{
    public function setDefaultOptions(SomeResolver $resolver)
    {
        parent::setDefaultOptions($resolver);
    }
}

class AnotherClass
{
    public function setDefaultOptions(SomeResolver $resolver)
    {
        parent::setDefaultOptions($resolver);
    }
}

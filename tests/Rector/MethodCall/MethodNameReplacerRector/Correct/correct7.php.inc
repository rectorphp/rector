<?php declare (strict_types=1);

use Rector\Tests\Rector\MethodCall\MethodNameReplacerRector\Source\AbstractType;

class MyType extends AbstractType
{
    public function configureOptions(SomeResolver $resolver)
    {
        $something = new Something();
        $something->setDefaultOptions();

        parent::configureOptions($resolver);
    }
}

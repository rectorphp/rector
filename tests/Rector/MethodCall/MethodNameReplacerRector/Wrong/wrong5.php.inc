<?php

namespace Rector\Tests\Rector\MethodCall\MethodNameReplacerRector\Wrong;

use Rector\Tests\Rector\MethodCall\MethodNameReplacerRector\Source\AbstractType;
use Rector\Tests\Rector\MethodCall\MethodNameReplacerRector\Source\Something;

class MyType5 extends AbstractType
{
    public function setDefaultOptions(SomeResolver $resolver)
    {
        $something = new Something();
        $something->setDefaultOptions();

        parent::setDefaultOptions($resolver);
    }
}

?>
-----
<?php

namespace Rector\Tests\Rector\MethodCall\MethodNameReplacerRector\Wrong;

use Rector\Tests\Rector\MethodCall\MethodNameReplacerRector\Source\AbstractType;
use Rector\Tests\Rector\MethodCall\MethodNameReplacerRector\Source\Something;

class MyType5 extends AbstractType
{
    public function configureOptions(SomeResolver $resolver)
    {
        $something = new Something();
        $something->setDefaultOptions();

        parent::configureOptions($resolver);
    }
}

?>

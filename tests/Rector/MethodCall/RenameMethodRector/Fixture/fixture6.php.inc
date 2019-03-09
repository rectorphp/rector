<?php

namespace Rector\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

use Rector\Tests\Rector\MethodCall\RenameMethodRector\Source\AbstractType;
use Rector\Tests\Rector\MethodCall\RenameMethodRector\Source\Something;

class MyType6 extends AbstractType
{
    public function setDefaultOptions(SomeResolver $resolver)
    {
        $something = new Something();
        $something->setDefaultOptions();

        parent::setDEFAULTOptions($resolver);
    }
}

?>
-----
<?php

namespace Rector\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

use Rector\Tests\Rector\MethodCall\RenameMethodRector\Source\AbstractType;
use Rector\Tests\Rector\MethodCall\RenameMethodRector\Source\Something;

class MyType6 extends AbstractType
{
    public function configureOptions(SomeResolver $resolver)
    {
        $something = new Something();
        $something->setDefaultOptions();

        parent::configureOptions($resolver);
    }
}

?>

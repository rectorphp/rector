<?php

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

use Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Source\AbstractType;
use Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Source\Something;

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

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

use Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Source\AbstractType;
use Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Source\Something;

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

<?php

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

use Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Source\AbstractType;

class MyCustomType extends AbstractType
{
    public function setDefaultOptions(SomeResolver $resolver)
    {
        parent::setDefaultOptions($resolver);
    }
}

class AnotherClass extends \DateTime
{
    public function setDefaultOptions(SomeResolver $resolver)
    {
        parent::setDefaultOptions($resolver);
    }
}

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

use Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Source\AbstractType;

class MyCustomType extends AbstractType
{
    public function configureOptions(SomeResolver $resolver)
    {
        parent::configureOptions($resolver);
    }
}

class AnotherClass extends \DateTime
{
    public function setDefaultOptions(SomeResolver $resolver)
    {
        parent::setDefaultOptions($resolver);
    }
}

?>

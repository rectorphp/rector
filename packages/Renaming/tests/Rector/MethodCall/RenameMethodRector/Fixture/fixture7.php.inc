<?php

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

use Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Source\AbstractType;

class SomeClass8
{
    /** @var AbstractType */
    protected $someOtherClass;

    public function __construct(AbstractType $someOtherClass)
    {
        $this->someOtherClass = $someOtherClass;
    }

    public function doSomething()
    {
        $this->someOtherClass->setDefaultOptions();
    }
}

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

use Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Source\AbstractType;

class SomeClass8
{
    /** @var AbstractType */
    protected $someOtherClass;

    public function __construct(AbstractType $someOtherClass)
    {
        $this->someOtherClass = $someOtherClass;
    }

    public function doSomething()
    {
        $this->someOtherClass->configureOptions();
    }
}

?>

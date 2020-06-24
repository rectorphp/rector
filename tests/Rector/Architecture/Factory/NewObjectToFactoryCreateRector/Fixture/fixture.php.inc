<?php

namespace Rector\Core\Tests\Rector\Architecture\Factory\NewObjectToFactoryCreateRector\Fixture;

use Rector\Core\Tests\Rector\Architecture\Factory\NewObjectToFactoryCreateRector\Source\MyClass;

final class SomeController
{
    public function default()
    {
        new MyClass('abcd');
        $class = new MyClass('abcd');
    }
}
?>
-----
<?php

namespace Rector\Core\Tests\Rector\Architecture\Factory\NewObjectToFactoryCreateRector\Fixture;

use Rector\Core\Tests\Rector\Architecture\Factory\NewObjectToFactoryCreateRector\Source\MyClass;

final class SomeController
{
    private \Rector\Core\Tests\Rector\Architecture\Factory\NewObjectToFactoryCreateRector\Source\MyClassFactory $myClassFactory;
    public function __construct(\Rector\Core\Tests\Rector\Architecture\Factory\NewObjectToFactoryCreateRector\Source\MyClassFactory $myClassFactory)
    {
        $this->myClassFactory = $myClassFactory;
    }
    public function default()
    {
        $this->myClassFactory->create('abcd');
        $class = $this->myClassFactory->create('abcd');
    }
}
?>

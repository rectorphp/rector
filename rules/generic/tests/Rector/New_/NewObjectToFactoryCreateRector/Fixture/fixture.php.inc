<?php

namespace Rector\Generic\Tests\Rector\New_\NewObjectToFactoryCreateRector\Fixture;

use Rector\Generic\Tests\Rector\New_\NewObjectToFactoryCreateRector\Source\MyClass;

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

namespace Rector\Generic\Tests\Rector\New_\NewObjectToFactoryCreateRector\Fixture;

use Rector\Generic\Tests\Rector\New_\NewObjectToFactoryCreateRector\Source\MyClass;

final class SomeController
{
    private \Rector\Generic\Tests\Rector\New_\NewObjectToFactoryCreateRector\Source\MyClassFactory $myClassFactory;
    public function __construct(\Rector\Generic\Tests\Rector\New_\NewObjectToFactoryCreateRector\Source\MyClassFactory $myClassFactory)
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

<?php

namespace Rector\Tests\Transform\Rector\New_\NewToMethodCallRector\Fixture;

use Rector\Tests\Transform\Rector\New_\NewToMethodCallRector\Source\MyClass;

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

namespace Rector\Tests\Transform\Rector\New_\NewToMethodCallRector\Fixture;

use Rector\Tests\Transform\Rector\New_\NewToMethodCallRector\Source\MyClass;

final class SomeController
{
    public function __construct(private \Rector\Tests\Transform\Rector\New_\NewToMethodCallRector\Source\MyClassFactory $myClassFactory)
    {
    }
    public function default()
    {
        $this->myClassFactory->create('abcd');
        $class = $this->myClassFactory->create('abcd');
    }
}
?>

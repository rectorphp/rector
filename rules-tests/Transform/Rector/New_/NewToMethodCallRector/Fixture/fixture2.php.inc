<?php

namespace Rector\Tests\Transform\Rector\New_\NewToMethodCallRector\Fixture;

use Rector\Tests\Transform\Rector\New_\NewToMethodCallRector\Source\MyClass;

final class SomeOtherController
{
    /**
     * @var \Rector\Tests\Transform\Rector\New_\NewToMethodCallRector\Source\MyClassFactory
     */
    private $mySomeFactory;
    public function __construct(\Rector\Tests\Transform\Rector\New_\NewToMethodCallRector\Source\MyClassFactory $mySomeFactory)
    {
        $this->mySomeFactory = $mySomeFactory;
    }
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

final class SomeOtherController
{
    /**
     * @var \Rector\Tests\Transform\Rector\New_\NewToMethodCallRector\Source\MyClassFactory
     */
    private $mySomeFactory;
    public function __construct(\Rector\Tests\Transform\Rector\New_\NewToMethodCallRector\Source\MyClassFactory $mySomeFactory)
    {
        $this->mySomeFactory = $mySomeFactory;
    }
    public function default()
    {
        $this->mySomeFactory->create('abcd');
        $class = $this->mySomeFactory->create('abcd');
    }
}
?>

<?php

namespace Rector\Transform\Tests\Rector\New_\NewToMethodCallRector\Fixture;

use Rector\Transform\Tests\Rector\New_\NewToMethodCallRector\Source\MyClass;

final class SomeOtherController
{
    /**
     * @var \Rector\Transform\Tests\Rector\New_\NewToMethodCallRector\Source\MyClassFactory
     */
    private $mySomeFactory;
    public function __construct(\Rector\Transform\Tests\Rector\New_\NewToMethodCallRector\Source\MyClassFactory $mySomeFactory)
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

namespace Rector\Transform\Tests\Rector\New_\NewToMethodCallRector\Fixture;

use Rector\Transform\Tests\Rector\New_\NewToMethodCallRector\Source\MyClass;

final class SomeOtherController
{
    /**
     * @var \Rector\Transform\Tests\Rector\New_\NewToMethodCallRector\Source\MyClassFactory
     */
    private $mySomeFactory;
    public function __construct(\Rector\Transform\Tests\Rector\New_\NewToMethodCallRector\Source\MyClassFactory $mySomeFactory)
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

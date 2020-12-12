<?php

namespace Rector\Generic\Tests\Rector\New_\NewObjectToFactoryCreateRector\Fixture;

use Rector\Generic\Tests\Rector\New_\NewObjectToFactoryCreateRector\Source\MyClass;

final class SomeOtherController
{
    /**
     * @var \Rector\Generic\Tests\Rector\New_\NewObjectToFactoryCreateRector\Source\MyClassFactory
     */
    private $mySomeFactory;
    public function __construct(\Rector\Generic\Tests\Rector\New_\NewObjectToFactoryCreateRector\Source\MyClassFactory $mySomeFactory)
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

namespace Rector\Generic\Tests\Rector\New_\NewObjectToFactoryCreateRector\Fixture;

use Rector\Generic\Tests\Rector\New_\NewObjectToFactoryCreateRector\Source\MyClass;

final class SomeOtherController
{
    /**
     * @var \Rector\Generic\Tests\Rector\New_\NewObjectToFactoryCreateRector\Source\MyClassFactory
     */
    private $mySomeFactory;
    public function __construct(\Rector\Generic\Tests\Rector\New_\NewObjectToFactoryCreateRector\Source\MyClassFactory $mySomeFactory)
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

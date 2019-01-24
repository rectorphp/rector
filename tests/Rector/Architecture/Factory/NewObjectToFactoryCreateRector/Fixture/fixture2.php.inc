<?php

namespace Rector\Tests\Rector\Architecture\Factory\NewObjectToFactoryCreateRector\Fixture;

use Rector\Tests\Rector\Architecture\Factory\NewObjectToFactoryCreateRector\Source\MyClass;

final class SomeOtherController
{
    /**
     * @var \Rector\Tests\Rector\Architecture\Factory\NewObjectToFactoryCreateRector\Source\MyClassFactory
     */
    private $mySomeFactory;
    public function __construct(\Rector\Tests\Rector\Architecture\Factory\NewObjectToFactoryCreateRector\Source\MyClassFactory $mySomeFactory)
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

namespace Rector\Tests\Rector\Architecture\Factory\NewObjectToFactoryCreateRector\Fixture;

use Rector\Tests\Rector\Architecture\Factory\NewObjectToFactoryCreateRector\Source\MyClass;

final class SomeOtherController
{
    /**
     * @var \Rector\Tests\Rector\Architecture\Factory\NewObjectToFactoryCreateRector\Source\MyClassFactory
     */
    private $mySomeFactory;
    public function __construct(\Rector\Tests\Rector\Architecture\Factory\NewObjectToFactoryCreateRector\Source\MyClassFactory $mySomeFactory)
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

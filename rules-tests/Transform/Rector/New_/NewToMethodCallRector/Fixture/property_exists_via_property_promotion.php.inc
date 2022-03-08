<?php

namespace Rector\Tests\Transform\Rector\New_\NewToMethodCallRector\Fixture;

use Rector\Tests\Transform\Rector\New_\NewToMethodCallRector\Source\MyClass;

final class PropertyExistsViaPropertyPromotion
{
    public function __construct(private \Rector\Tests\Transform\Rector\New_\NewToMethodCallRector\Source\MyClassFactory $mySomeFactory)
    {
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

final class PropertyExistsViaPropertyPromotion
{
    public function __construct(private \Rector\Tests\Transform\Rector\New_\NewToMethodCallRector\Source\MyClassFactory $mySomeFactory)
    {
    }
    public function default()
    {
        $this->mySomeFactory->create('abcd');
        $class = $this->mySomeFactory->create('abcd');
    }
}
?>

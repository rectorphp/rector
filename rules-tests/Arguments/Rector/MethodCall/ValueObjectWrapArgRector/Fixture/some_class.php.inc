<?php

namespace Rector\Tests\Arguments\Rector\MethodCall\ValueObjectWrapArgRector\Fixture;

class SomeClass
{
    public function run()
    {
        $this->something(1000);
    }
}

?>
-----
<?php

namespace Rector\Tests\Arguments\Rector\MethodCall\ValueObjectWrapArgRector\Fixture;

class SomeClass
{
    public function run()
    {
        $this->something(new \Number(1000));
    }
}

?>

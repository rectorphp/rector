<?php

namespace Rector\Tests\Transform\Rector\MethodCall\MethodCallToMethodCallRector\Fixture;

class AClass
{
    public function run()
    {
        $this->methodFromAClass();
    }

    public function methodFromAClass()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\MethodCall\MethodCallToMethodCallRector\Fixture;

class AClass
{
    public function __construct(private \Rector\Tests\Transform\Rector\MethodCall\MethodCallToMethodCallRector\Fixture\AnotherClass $anotherClass)
    {
    }
    public function run()
    {
        $this->anotherClass->methodFromAnotherClass();
    }

    public function methodFromAClass()
    {
    }
}

?>

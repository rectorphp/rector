<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveSetterOnlyPropertyAndMethodCallRector\Fixture;

class SomeClass
{
    private $name;

    public function setName($name)
    {
        $this->name = $name;
    }
}

class ActiveOnlySetter
{
    public function run()
    {
        $someClass = new SomeClass();
        $someClass->setName('Tom');
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveSetterOnlyPropertyAndMethodCallRector\Fixture;

class SomeClass
{
}

class ActiveOnlySetter
{
    public function run()
    {
        $someClass = new SomeClass();
    }
}

?>

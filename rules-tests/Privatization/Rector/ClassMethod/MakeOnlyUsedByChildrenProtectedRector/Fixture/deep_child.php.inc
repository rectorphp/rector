<?php

namespace Rector\Tests\Privatization\Rector\ClassMethod\MakeOnlyUsedByChildrenProtectedRector\Fixture;

class AbstractSomeClassTop
{
    public function run()
    {
    }
}

class AbstractSomeClassSecond extends AbstractSomeClassTop
{
}

class DeepChild extends AbstractSomeClassSecond
{
    public function go()
    {
        $this->run();
    }
}

?>
-----
<?php

namespace Rector\Tests\Privatization\Rector\ClassMethod\MakeOnlyUsedByChildrenProtectedRector\Fixture;

class AbstractSomeClassTop
{
    protected function run()
    {
    }
}

class AbstractSomeClassSecond extends AbstractSomeClassTop
{
}

class DeepChild extends AbstractSomeClassSecond
{
    public function go()
    {
        $this->run();
    }
}

?>

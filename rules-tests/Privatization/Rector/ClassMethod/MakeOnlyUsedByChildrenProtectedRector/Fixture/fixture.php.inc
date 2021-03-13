<?php

namespace Rector\Tests\Privatization\Rector\ClassMethod\MakeOnlyUsedByChildrenProtectedRector\Fixture;

class AbstractSomeClass
{
    public function run()
    {
    }
}

class Fixture extends AbstractSomeClass
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

class AbstractSomeClass
{
    protected function run()
    {
    }
}

class Fixture extends AbstractSomeClass
{
    public function go()
    {
        $this->run();
    }
}

?>

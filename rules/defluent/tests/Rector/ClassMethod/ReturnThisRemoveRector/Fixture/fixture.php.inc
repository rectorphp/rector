<?php

namespace Rector\Defluent\Tests\Rector\ClassMethod\ReturnThisRemoveRector\Fixture;

class Fixture
{
    /**
     * @return AnyClass
     */
    public function someFunction()
    {
        foo();
        return $this;
    }

    /**
     * @return AnyClass
     */
    public function otherFunction()
    {
        bar();
        return $this;
    }
}

?>
-----
<?php

namespace Rector\Defluent\Tests\Rector\ClassMethod\ReturnThisRemoveRector\Fixture;

class Fixture
{
    public function someFunction(): void
    {
        foo();
    }

    public function otherFunction(): void
    {
        bar();
    }
}

?>

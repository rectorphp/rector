<?php

namespace Rector\Core\Tests\Rector\MethodBody\ReturnThisRemoveRector\Fixture;

class SomeClass
{
    /**
     * @return SomeClass
     */
    public function someFunction()
    {
        foo();
        return $this;
    }

    /**
     * @return SomeClass
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

namespace Rector\Core\Tests\Rector\MethodBody\ReturnThisRemoveRector\Fixture;

class SomeClass
{
    public function someFunction()
    {
        foo();
    }

    public function otherFunction()
    {
        bar();
    }
}

?>

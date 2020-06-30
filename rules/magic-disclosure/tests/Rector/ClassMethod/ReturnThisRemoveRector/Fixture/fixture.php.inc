<?php

namespace Rector\MagicDisclosure\Tests\Rector\ClassMethod\ReturnThisRemoveRector\Fixture;

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

namespace Rector\MagicDisclosure\Tests\Rector\ClassMethod\ReturnThisRemoveRector\Fixture;

class SomeClass
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

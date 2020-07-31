<?php

namespace Rector\MagicDisclosure\Tests\Rector\ClassMethod\ReturnThisRemoveRector\FixtureConfiguration;

class SomeClass
{
    /**
     * @return self
     */
    public function someFunction()
    {
        foo();
        return $this;
    }

    /**
     * @return self
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

namespace Rector\MagicDisclosure\Tests\Rector\ClassMethod\ReturnThisRemoveRector\FixtureConfiguration;

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

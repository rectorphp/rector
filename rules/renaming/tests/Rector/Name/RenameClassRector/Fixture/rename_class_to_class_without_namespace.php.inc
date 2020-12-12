<?php

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector\Fixture;

class AnotherMyClass
{
    /**
     * @return AnotherMyClass
     */
    public function createSelf(): AnotherMyClass
    {
        return new AnotherMyClass;
    }
}

?>
-----
<?php

class MyNewClassWithoutNamespace
{
    /**
     * @return \MyNewClassWithoutNamespace
     */
    public function createSelf(): \MyNewClassWithoutNamespace
    {
        return new \MyNewClassWithoutNamespace;
    }
}

?>

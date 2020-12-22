<?php

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector\Fixture;

class RenameClass
{
    /**
     * @return MyClass
     */
    public function createSelf(): MyClass
    {
        return new MyClass;
    }
}

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector\Fixture;

class MyNewClass
{
    /**
     * @return \MyNewNamespace\MyNewClass
     */
    public function createSelf(): \MyNewNamespace\MyNewClass
    {
        return new \MyNewNamespace\MyNewClass;
    }
}

?>

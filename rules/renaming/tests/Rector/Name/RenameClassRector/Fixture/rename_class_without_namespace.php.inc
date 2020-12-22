<?php

class RenameClassWithoutNamespace
{
    /**
     * @return MyOldClass
     */
    public function createSelf(): MyOldClass
    {
        return new MyOldClass;
    }
}

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector\Fixture;

class MyNewClass
{
    /**
     * @return \MyNamespace\MyNewClass
     */
    public function createSelf(): \MyNamespace\MyNewClass
    {
        return new \MyNamespace\MyNewClass;
    }
}

?>

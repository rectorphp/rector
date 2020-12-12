<?php

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector\Fixture;

interface MyInterface
{
    /**
     * @return MyInterface
     */
    public function createSelf(): MyInterface;
}

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector\Fixture;

interface MyNewInterface
{
    /**
     * @return \MyNewNamespace\MyNewInterface
     */
    public function createSelf(): \MyNewNamespace\MyNewInterface;
}

?>

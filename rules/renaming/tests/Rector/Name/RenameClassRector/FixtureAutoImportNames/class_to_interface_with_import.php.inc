<?php

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector\FixtureAutoImportNames;

use Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\SomeClass;

class ClassToInterfaceWithImport
{
    protected SomeClass $someClass;

    public function __construct(
        SomeClass $someClass
    ) {
        $this->someClass = $someClass;
    }
}

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector\FixtureAutoImportNames;

use Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\SomeClassInterface;

class ClassToInterfaceWithImport
{
    protected SomeClassInterface $someClass;

    public function __construct(
        SomeClassInterface $someClass
    ) {
        $this->someClass = $someClass;
    }
}

?>

<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\FixtureAutoImportNames;

use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\FirstNamespace\SomeServiceClass;

class SomeClass
{
    public function aFunction()
    {
        $someService = new SomeServiceClass();
    }
}

?>
-----
<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\FixtureAutoImportNames;

use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\SecondNamespace\SomeServiceClass;

class SomeClass
{
    public function aFunction()
    {
        $someService = new SomeServiceClass();
    }
}

?>

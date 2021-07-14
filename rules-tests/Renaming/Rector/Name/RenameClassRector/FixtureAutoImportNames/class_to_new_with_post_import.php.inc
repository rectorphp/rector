<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\FixtureAutoImportNames;

use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\OldClass;

class ClassToNewWithPostImport
{
    public function run()
    {
        return new OldClass();
    }
}

?>
-----
<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\FixtureAutoImportNames;

use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\NewClass;

class ClassToNewWithPostImport
{
    public function run()
    {
        return new NewClass();
    }
}

?>

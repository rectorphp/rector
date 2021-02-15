<?php

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector\FixtureAutoImportNames;

use Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\OldClass;

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

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector\FixtureAutoImportNames;

use Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\NewClass;
use Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\OldClass;

class ClassToNewWithPostImport
{
    public function run()
    {
        return new NewClass();
    }
}

?>

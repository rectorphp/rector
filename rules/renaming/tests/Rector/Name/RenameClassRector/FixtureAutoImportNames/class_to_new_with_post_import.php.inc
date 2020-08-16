<?php

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector\Fixture\AutoImportNamesParameter;

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

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector\Fixture\AutoImportNamesParameter;

use Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\NewClass;

class ClassToNewWithPostImport
{
    public function run()
    {
        return new NewClass();
    }
}

?>

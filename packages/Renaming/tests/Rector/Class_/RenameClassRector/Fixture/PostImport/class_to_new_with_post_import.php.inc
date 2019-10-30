<?php

namespace Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Fixture\PostImport;

use Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Source\OldClass;

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

namespace Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Fixture\PostImport;

use Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Source\NewClass;

class ClassToNewWithPostImport
{
    public function run()
    {
        return new NewClass();
    }
}

?>

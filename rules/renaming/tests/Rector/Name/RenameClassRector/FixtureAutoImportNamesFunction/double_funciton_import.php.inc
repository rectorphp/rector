<?php

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector\Fixture\FixtureAutoImportNamesParameter;

class DoubleFunctionImport
{
    public function create()
    {
        return \Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\some_another_function(1, 2);
    }

    public function emulate()
    {
        return \Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\some_another_function(4, 5);
    }
}

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector\Fixture\FixtureAutoImportNamesParameter;

use function Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\some_another_function;
class DoubleFunctionImport
{
    public function create()
    {
        return some_another_function(1, 2);
    }

    public function emulate()
    {
        return some_another_function(4, 5);
    }
}

?>

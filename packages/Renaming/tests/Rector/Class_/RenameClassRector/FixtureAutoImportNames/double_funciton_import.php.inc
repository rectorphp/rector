<?php

namespace Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Fixture\AutoImportNamesParameter;

class DoubleFunctionImport
{
    public function create()
    {
        return \Safe\substr(1, 2);
    }

    public function emulate()
    {
        return \Safe\substr(4, 5);
    }
}

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Fixture\AutoImportNamesParameter;

use function Safe\substr;
class DoubleFunctionImport
{
    public function create()
    {
        return substr(1, 2);
    }

    public function emulate()
    {
        return substr(4, 5);
    }
}

?>

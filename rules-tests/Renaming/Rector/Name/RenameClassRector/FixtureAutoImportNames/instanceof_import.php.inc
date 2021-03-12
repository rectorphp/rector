<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\FixtureAutoImportNames;

use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\OldClass;

final class InstanceofImport
{
    public function run($value)
    {
        if ($value instanceof OldClass) {
            return true;
        }

        return false;
    }
}

?>
-----
<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\FixtureAutoImportNames;

use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\NewClass;
use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\OldClass;

final class InstanceofImport
{
    public function run($value)
    {
        if ($value instanceof NewClass) {
            return true;
        }

        return false;
    }
}

?>

<?php

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector\Fixture;

use Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\OldClassWithTypo;

class NameInsensitive extends OldClassWithTypO
{
    public function run(): OLDClassWithTYPO
    {
        $oldClassWithTypo = new OldClassWithTYPO;
    }
}

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector\Fixture;

use Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\OldClassWithTypo;

class NameInsensitive extends \Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\NewClassWithoutTypo
{
    public function run(): \Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\NewClassWithoutTypo
    {
        $oldClassWithTypo = new \Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\NewClassWithoutTypo;
    }
}

?>

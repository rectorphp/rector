<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\Fixture;

use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\OldClassWithTypo;

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

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\Fixture;

use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\OldClassWithTypo;

class NameInsensitive extends \Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\NewClassWithoutTypo
{
    public function run(): \Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\NewClassWithoutTypo
    {
        $oldClassWithTypo = new \Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\NewClassWithoutTypo;
    }
}

?>

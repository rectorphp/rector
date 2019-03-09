<?php

namespace Rector\Tests\Rector\Class_\RenameClassRector\Fixture;

use Rector\Tests\Rector\Class_\RenameClassRector\Source\OldClassWithTypo;

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

namespace Rector\Tests\Rector\Class_\RenameClassRector\Fixture;

use \Rector\Tests\Rector\Class_\RenameClassRector\Source\NewClassWithoutTypo;

class NameInsensitive extends \Rector\Tests\Rector\Class_\RenameClassRector\Source\NewClassWithoutTypo
{
    public function run(): \Rector\Tests\Rector\Class_\RenameClassRector\Source\NewClassWithoutTypo
    {
        $oldClassWithTypo = new \Rector\Tests\Rector\Class_\RenameClassRector\Source\NewClassWithoutTypo;
    }
}

?>

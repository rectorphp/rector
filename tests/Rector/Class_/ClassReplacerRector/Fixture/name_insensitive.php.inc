<?php

namespace Rector\Tests\Rector\Class_\ClassReplacerRector\Fixture;

use Rector\Tests\Rector\Class_\ClassReplacerRector\Source\OldClassWithTypo;

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

namespace Rector\Tests\Rector\Class_\ClassReplacerRector\Fixture;

use \Rector\Tests\Rector\Class_\ClassReplacerRector\Source\NewClassWithoutTypo;

class NameInsensitive extends \Rector\Tests\Rector\Class_\ClassReplacerRector\Source\NewClassWithoutTypo
{
    public function run(): \Rector\Tests\Rector\Class_\ClassReplacerRector\Source\NewClassWithoutTypo
    {
        $oldClassWithTypo = new \Rector\Tests\Rector\Class_\ClassReplacerRector\Source\NewClassWithoutTypo;
    }
}

?>

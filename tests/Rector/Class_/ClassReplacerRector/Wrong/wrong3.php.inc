<?php

namespace SomeNamespace;

use Rector\Tests\Rector\Class_\ClassReplacerRector\Source\OldClassWithTypo;

class MyClass extends OldClassWithTypO
{
    public function run(): OLDClassWithTYPO
    {
        $oldClassWithTypo = new OldClassWithTYPO;
    }
}

?>
-----
<?php

namespace SomeNamespace;

use \SomeNamespace\NewClassWithoutTypo;

class MyClass extends \SomeNamespace\NewClassWithoutTypo
{
    public function run(): \SomeNamespace\NewClassWithoutTypo
    {
        $oldClassWithTypo = new \SomeNamespace\NewClassWithoutTypo;
    }
}

?>

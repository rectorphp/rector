<?php declare (strict_types=1);

namespace SomeNamespace;

use Rector\Tests\Rector\Class_\ClassReplacerRector\Source\OldClassWithTypo;

class MyClass extends OldClassWithTypO
{
    public function run(): OLDClassWithTYPO
    {
        $oldClassWithTypo = new OldClassWithTYPO;
    }
}

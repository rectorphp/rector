<?php declare (strict_types=1);

namespace SomeNamespace;

use \SomeNamespace\NewClassWithoutTypo;

class MyClass extends \SomeNamespace\NewClassWithoutTypo
{
    public function run(): \SomeNamespace\NewClassWithoutTypo
    {
        $oldClassWithTypo = new \SomeNamespace\NewClassWithoutTypo;
    }
}

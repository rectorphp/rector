<?php

namespace Rector\Tests\Rector\Namespace_\PseudoNamespaceToNamespaceRector\Wrong;

use PHPUnit_SpecialClass;

class SomeTest
{
    public function create()
    {
        $specialClass = new PHPUnit_SpecialClass;
    }
}

?>
-----
<?php

namespace Rector\Tests\Rector\Namespace_\PseudoNamespaceToNamespaceRector\Wrong;

use PHPUnit\SpecialClass;

class SomeTest
{
    public function create()
    {
        $specialClass = new \PHPUnit\SpecialClass;
    }
}

?>

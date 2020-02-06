<?php

namespace Rector\Core\Tests\Rector\Namespace_\PseudoNamespaceToNamespaceRector\Fixture;

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

namespace Rector\Core\Tests\Rector\Namespace_\PseudoNamespaceToNamespaceRector\Fixture;

use PHPUnit\SpecialClass;

class SomeTest
{
    public function create()
    {
        $specialClass = new \PHPUnit\SpecialClass;
    }
}

?>

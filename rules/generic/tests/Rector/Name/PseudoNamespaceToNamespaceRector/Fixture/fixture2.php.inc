<?php

namespace Rector\Generic\Tests\Rector\Name\PseudoNamespaceToNamespaceRector\Fixture;

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

namespace Rector\Generic\Tests\Rector\Name\PseudoNamespaceToNamespaceRector\Fixture;

use PHPUnit\SpecialClass;

class SomeTest
{
    public function create()
    {
        $specialClass = new \PHPUnit\SpecialClass;
    }
}

?>

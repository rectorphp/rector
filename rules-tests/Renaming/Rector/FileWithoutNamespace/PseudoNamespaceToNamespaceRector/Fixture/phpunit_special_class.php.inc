<?php

namespace Rector\Tests\Renaming\Rector\FileWithoutNamespace\PseudoNamespaceToNamespaceRector\Fixture;

use PHPUnit_SpecialClass;

class Fixture2
{
    public function create()
    {
        $specialClass = new PHPUnit_SpecialClass;
    }
}

?>
-----
<?php

namespace Rector\Tests\Renaming\Rector\FileWithoutNamespace\PseudoNamespaceToNamespaceRector\Fixture;

use PHPUnit\SpecialClass;

class Fixture2
{
    public function create()
    {
        $specialClass = new \PHPUnit\SpecialClass;
    }
}

?>

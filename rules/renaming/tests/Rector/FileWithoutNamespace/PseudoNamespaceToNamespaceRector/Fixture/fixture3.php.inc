<?php

class PHPUnit_Abc
{
    public function run()
    {
        return 1;
    }
}

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\FileWithoutNamespace\PseudoNamespaceToNamespaceRector\Fixture;

class Abc
{
    public function run()
    {
        return 1;
    }
}

?>

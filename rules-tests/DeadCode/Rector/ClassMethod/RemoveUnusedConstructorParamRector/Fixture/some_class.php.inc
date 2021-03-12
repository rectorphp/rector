<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedConstructorParamRector\Fixture;

final class SomeClass
{
    private $hey;

    public function __construct($hey, $man)
    {
        $this->hey = $hey;
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedConstructorParamRector\Fixture;

final class SomeClass
{
    private $hey;

    public function __construct($hey)
    {
        $this->hey = $hey;
    }
}

?>

<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedConstructorParamRector\Fixture;

final class UsedInInnerClass
{
    private $hey;

    public function __construct($hey, $man)
    {
        $this->hey = $hey;

        new class {
            public function __construct($man)
            {

            }
        };
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedConstructorParamRector\Fixture;

final class UsedInInnerClass
{
    private $hey;

    public function __construct($hey)
    {
        $this->hey = $hey;

        new class {
            public function __construct()
            {
            }
        };
    }
}

?>

<?php

namespace Rector\Core\Tests\Rector\Exclusion\Check\DocBlockOnSelfBaseLine;


final class SomeController
{
    public function foo()
    {

    }
    public function bar()
    {

    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Rector\Exclusion\Check\DocBlockOnSelfBaseLine;


final class SomeController
{
}

?>

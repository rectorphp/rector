<?php

namespace Rector\Tests\Rector\Exclusion\Check\Fixture1;


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

namespace Rector\Tests\Rector\Exclusion\Check\Fixture1;


final class SomeController
{
}

?>

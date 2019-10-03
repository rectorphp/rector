<?php

namespace Rector\Tests\Rector\Exclusion\Check\Fixture2;


final class SomeController
{
    public function foo()
    {
        round(1 + 0);
        round(1 + 0);
    }
}

?>
-----
<?php

namespace Rector\Tests\Rector\Exclusion\Check\Fixture2;


final class SomeController
{
    public function foo()
    {
        round(1);
        round(1);
    }
}

?>

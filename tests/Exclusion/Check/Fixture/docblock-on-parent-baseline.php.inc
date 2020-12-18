<?php

namespace Rector\Core\Tests\Rector\Exclusion\Check\DocBlockOnParentBaseLine;


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

namespace Rector\Core\Tests\Rector\Exclusion\Check\DocBlockOnParentBaseLine;


final class SomeController
{
    public function foo()
    {
        round(1);
        round(1);
    }
}

?>

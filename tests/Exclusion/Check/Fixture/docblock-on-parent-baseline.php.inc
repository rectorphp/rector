<?php

namespace Rector\Core\Tests\Exclusion\Check\Fixture;

final class DocblockOnParentBaseline
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

namespace Rector\Core\Tests\Exclusion\Check\Fixture;

final class DocblockOnParentBaseline
{
    public function foo()
    {
        round(1);
        round(1);
    }
}

?>

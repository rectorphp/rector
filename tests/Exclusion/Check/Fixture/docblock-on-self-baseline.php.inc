<?php

namespace Rector\Core\Tests\Exclusion\Check\Fixture;

final class DocblockOnSelfBaseline
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

namespace Rector\Core\Tests\Exclusion\Check\Fixture;

final class DocblockOnSelfBaseline
{
}

?>

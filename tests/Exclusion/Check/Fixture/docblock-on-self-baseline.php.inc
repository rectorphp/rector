<?php

namespace Rector\Core\Tests\Exclusion\Check\Fixture;


final class Docblock-on-self-baseline
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


final class Docblock-on-self-baseline
{
}

?>

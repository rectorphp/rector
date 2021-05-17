<?php

namespace Rector\Core\Tests\Issues\Issue6420\Fixture;

class Fixture
{
    public function someMethod()
    {
        preg_replace('//', 'foo', 'bar');
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\Issue6420\Fixture;

class Fixture
{
    public function someMethod()
    {
        \Safe\preg_replace('//', 'foo', 'bar');
    }
}

?>

<?php

namespace Rector\Legacy\Tests\Rector\Include_\RemoveIncludeRector\Fixture;

class SomeClass
{

    private function someMethod() : void
    {
        // comment before include
include 'somefile.php';
        // comment after include
    }

}
?>
-----
<?php

namespace Rector\Legacy\Tests\Rector\Include_\RemoveIncludeRector\Fixture;

class SomeClass
{

    private function someMethod() : void
    {
        // comment before include

        // comment after include
    }

}
?>

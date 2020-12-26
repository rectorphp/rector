<?php

namespace Rector\DeadCode\Tests\Rector\FunctionLike\RemoveCodeAfterReturnRector\Fixture;

final class DemoFile
{
    public function run()
    {
        return 5;

        // we never get here
        return 10;
    }
}
?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\FunctionLike\RemoveCodeAfterReturnRector\Fixture;

// what is expected code?
// should remain the same? delete part bellow ----- (included)

?>

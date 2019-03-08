<?php

namespace Rector\Tests\Rector\Argument\ArgumentRemoverRector\Fixture;

final class RemoveInMiddle
{
    public function run($first, $second, $third)
    {

    }
}

?>
-----
<?php

namespace Rector\Tests\Rector\Argument\ArgumentRemoverRector\Fixture;

final class RemoveInMiddle
{
    public function run($first, $third)
    {

    }
}

?>

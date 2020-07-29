<?php

namespace Rector\Generic\Tests\Rector\Argument\ArgumentRemoverRector\Fixture;

use Rector\Generic\Tests\Rector\Argument\ArgumentRemoverRector\Source\RemoveInTheMiddle;

final class RemoveInMiddle extends RemoveInTheMiddle
{
    public function run($first, $second, $third)
    {

    }
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Argument\ArgumentRemoverRector\Fixture;

use Rector\Generic\Tests\Rector\Argument\ArgumentRemoverRector\Source\RemoveInTheMiddle;

final class RemoveInMiddle extends RemoveInTheMiddle
{
    public function run($first, $third)
    {

    }
}

?>

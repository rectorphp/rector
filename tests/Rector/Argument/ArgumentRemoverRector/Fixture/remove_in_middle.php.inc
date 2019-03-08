<?php

namespace Rector\Tests\Rector\Argument\ArgumentRemoverRector\Fixture;

use Rector\Tests\Rector\Argument\ArgumentRemoverRector\Source\RemoveInTheMiddle;

final class RemoveInMiddle extends RemoveInTheMiddle
{
    public function run($first, $second, $third)
    {

    }
}

?>
-----
<?php

namespace Rector\Tests\Rector\Argument\ArgumentRemoverRector\Fixture;

use Rector\Tests\Rector\Argument\ArgumentRemoverRector\Source\RemoveInTheMiddle;

final class RemoveInMiddle extends RemoveInTheMiddle
{
    public function run($first, $third)
    {

    }
}

?>

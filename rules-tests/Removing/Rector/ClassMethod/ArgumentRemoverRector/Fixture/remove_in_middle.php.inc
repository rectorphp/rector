<?php

namespace Rector\Removing\Tests\Rector\ClassMethod\ArgumentRemoverRector\Fixture;

use Rector\Removing\Tests\Rector\ClassMethod\ArgumentRemoverRector\Source\RemoveInTheMiddle;

final class RemoveInMiddle extends RemoveInTheMiddle
{
    public function run($first, $second, $third)
    {

    }
}

?>
-----
<?php

namespace Rector\Removing\Tests\Rector\ClassMethod\ArgumentRemoverRector\Fixture;

use Rector\Removing\Tests\Rector\ClassMethod\ArgumentRemoverRector\Source\RemoveInTheMiddle;

final class RemoveInMiddle extends RemoveInTheMiddle
{
    public function run($first, $third)
    {

    }
}

?>

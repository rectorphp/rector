<?php

namespace Rector\Tests\Removing\Rector\ClassMethod\ArgumentRemoverRector\Fixture;

use Rector\Tests\Removing\Rector\ClassMethod\ArgumentRemoverRector\Source\RemoveInTheMiddle;

final class RemoveInMiddle extends RemoveInTheMiddle
{
    public function run($first, $second, $third)
    {

    }
}

?>
-----
<?php

namespace Rector\Tests\Removing\Rector\ClassMethod\ArgumentRemoverRector\Fixture;

use Rector\Tests\Removing\Rector\ClassMethod\ArgumentRemoverRector\Source\RemoveInTheMiddle;

final class RemoveInMiddle extends RemoveInTheMiddle
{
    public function run($first, $third)
    {

    }
}

?>

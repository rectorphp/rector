<?php

namespace Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeThrowExprRector\Fixture;

use RuntimeException;

class CoalesceWithoutAssign
{
    public function run()
    {
        $somethingNonexistent ?? throw new RuntimeException();
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeThrowExprRector\Fixture;

use RuntimeException;

class CoalesceWithoutAssign
{
    public function run()
    {
        if (!isset($somethingNonexistent)) {
            throw new RuntimeException();
        }
    }
}

?>

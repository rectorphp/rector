<?php

namespace Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeThrowExprRector\Fixture;

use RuntimeException;

class AssignDirectThrow
{
    public function run()
    {
        $id = throw new RuntimeException();
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeThrowExprRector\Fixture;

use RuntimeException;

class AssignDirectThrow
{
    public function run()
    {
        throw new RuntimeException();
    }
}

?>

<?php

namespace Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeThrowExprRector\Fixture;

use RuntimeException;

class TruthyTernaryWithoutAssign
{
    public function run()
    {
        $var = 'test';
        $var ?: throw new RuntimeException();
    }

    public function run2()
    {
        $var = 'test';
        $var ? 'value' : throw new RuntimeException();
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeThrowExprRector\Fixture;

use RuntimeException;

class TruthyTernaryWithoutAssign
{
    public function run()
    {
        $var = 'test';
        if (!$var) {
            throw new RuntimeException();
        }
    }

    public function run2()
    {
        $var = 'test';
        if (!$var) {
            throw new RuntimeException();
        }
    }
}

?>

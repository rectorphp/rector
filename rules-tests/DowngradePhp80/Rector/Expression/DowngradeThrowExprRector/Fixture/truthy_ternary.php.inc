<?php

namespace Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeThrowExprRector\Fixture;

use RuntimeException;

class TruthyTernary
{
    public function run()
    {
        $var = 'test';
        $id = $var ?: throw new RuntimeException();
    }

    public function run2()
    {
        $var = 'test';
        $id = $var ? 'value' : throw new RuntimeException();
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeThrowExprRector\Fixture;

use RuntimeException;

class TruthyTernary
{
    public function run()
    {
        $var = 'test';
        if (!$var) {
            throw new RuntimeException();
        }
        $id = $var;
    }

    public function run2()
    {
        $var = 'test';
        if (!$var) {
            throw new RuntimeException();
        }
        $id = 'value';
    }
}

?>

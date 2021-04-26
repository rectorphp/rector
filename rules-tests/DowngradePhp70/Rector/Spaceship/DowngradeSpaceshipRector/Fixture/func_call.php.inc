<?php

namespace Rector\Tests\DowngradePhp70\Rector\Coalesce\DowngradeStrictTypeDeclarationRector\Fixture;

function getRand()
{
    return rand(1,100);
}

class FuncCall
{
    public function run()
    {
        return getRand() <=> getRand();
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\Coalesce\DowngradeStrictTypeDeclarationRector\Fixture;

function getRand()
{
    return rand(1,100);
}

class FuncCall
{
    public function run()
    {
        return (function ($left, $right) {
            if ($left === $right) {
                return 0;
            }
            return $left < $right ? -1 : 1;
        })(getRand(), getRand());
    }
}

?>
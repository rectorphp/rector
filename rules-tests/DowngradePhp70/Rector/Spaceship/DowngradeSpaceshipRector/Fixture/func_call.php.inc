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
        return (function ($a, $b) {
            if ($a === $b) {
                return 0;
            }
            return $a < $b ? -1 : 1;
        })(getRand(), getRand());
    }
}

?>
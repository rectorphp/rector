<?php

namespace Rector\Tests\DowngradePhp70\Rector\Coalesce\DowngradeStrictTypeDeclarationRector\Fixture;

class UseEcho
{
    public function run($a, $b)
    {
        echo $a <=> $b;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\Coalesce\DowngradeStrictTypeDeclarationRector\Fixture;

class UseEcho
{
    public function run($a, $b)
    {
        echo (function ($a, $b) {
            if ($a === $b) {
                return 0;
            }
            return $a < $b ? -1 : 1;
        })($a, $b);
    }
}

?>

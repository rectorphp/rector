<?php

namespace Rector\Tests\DowngradePhp70\Rector\Coalesce\DowngradeStrictTypeDeclarationRector\Fixture;

class InIf
{
    public function run($a, $b)
    {
        if ($a <=> $b) {
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\Coalesce\DowngradeStrictTypeDeclarationRector\Fixture;

class InIf
{
    public function run($a, $b)
    {
        if ((function ($a, $b) {
            if ($a === $b) {
                return 0;
            }
            return $a < $b ? -1 : 1;
        })($a, $b)) {
        }
    }
}

?>

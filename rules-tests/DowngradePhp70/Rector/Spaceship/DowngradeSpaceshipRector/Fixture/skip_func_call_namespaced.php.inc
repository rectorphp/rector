<?php

namespace Rector\Tests\DowngradePhp70\Rector\Coalesce\DowngradeStrictTypeDeclarationRector\Fixture;

function getRand()
{
    return rand(1,100);
}

class SkipFuncCall
{
    public function run()
    {
        return getRand() <=> getRand();
    }
}

?>


<?php

namespace Rector\Tests\DowngradePhp70\Rector\Coalesce\DowngradeStrictTypeDeclarationRector\Fixture;

function getRand2()
{
    return rand(1,100);
}

function getRand3()
{
    return rand(1,100);
}

class SkipFuncCallNamespaced2
{
    public function run()
    {
        return getRand2() <=> getRand3();
    }
}

?>

